import sys
import inspect
import json
import ast
import logging

logging.basicConfig(level=logging.DEBUG)   #,format='%(levelname)s:%(name)s:%(messages)s'

from actions import *


sys.path.append("./lib/python3.7/site-packages")

import pymongo
from bson import ObjectId
from pymongo.errors import *


class BaseElement(object):
	date = '15-5-2019'
	creation_date = ''
	_mydb = None
	fields = []
	
	def __init__(self, name):
		self._client = pymongo.MongoClient("mongodb://localhost:27017/")
		self._mydb = self._client["wf-engine"] 
		#self._mydb['workflows'].drop()   #Solo para depuracion
		self.name = name
		self.__dbBootstrap()

	def serializeProperties(self):
		s = {}
		for att in dir(self):
		 	if att in self.fields:
		 		val = '' if getattr(self,att) == None else getattr(self,att)
		 		s.update({ att: val})
		return s

	def execute(self):
		pass

	def __buildFromJSON(self):
		pass

	def serialize(self):
		pass

	def __dbBootstrap(self):
		pass

	def save(self):
		pass
	def load(self):
		print('Load')

class Workflow(BaseElement):
	#Atributos estaticos
	struct = None
	#Attributos serializable
	fields = ['id','name','description','cursor', "_globa_vars"]  #xw


	states = ["FINISHED","WAITING_CALLBACK",".RUNNING","ERROR","NO_EXECUTED"]

	_collection_name = 'workflows'

	@property
	def state(self):
		return self._state
	
	@state.setter
	def state(self,state):
		self._state = state

	@property
	def cursor(self):
		return self._cursor

	@property
	def activities(self):
		return self._activities


	def __init__(self, spec):
		name = '' if 'name' not in spec else spec['name']
		super(Workflow,self).__init__(name)
		self.id = None
		self._activities = {}
		#inicializar las referfencias a collecions
		self._collection = self._mydb[self._collection_name] 
		self._executions = self._mydb['executions']
		self._callbacks = self._mydb['callbacks']
		self._global_vars = {}
		self._executed_stages = {}
		self._input_params = {}
		self._cursor = ''
		self._state = "NO_EXECUTED"
		self._id = spec['_id'] if '_id' in spec else None
		self.instance_data = None
		#cargar colleccion de ejeciciones
		self._executions = self._mydb['executions']
		if self._id != None:
			self.load()
		else:
			self.description = spec['description']
			self.__loadActivities(spec)


	def __loadActivities(self,spec):
		act = spec['activities']
		for k, act_spec in spec['activities'].items():
			#Agreagra el nombre en la especificacion
			act_spec.update({"name" : k })
			activity = Activity(workflow=self,spec=act_spec)
			self.addActivity(activity)

	def addActivity(self,activity):
		self._activities.update( { activity.name : activity } )
	
	def __dbBootstrap(self):
		
		col = self._mydb.list_collection_names()
		if self._collection_name not in col:
			logging.debug('Bootstrap: Workflow')
			#Crear la collection
			self._collection = self._mydb[self._collection_name] 
			logging.debug('collection workflows created')
			self._collection.create_index(
				[('name', pymongo.ASCENDING)], unique=True, name='workflow_index', default_language='english')

	def __existsName(self):
		logging.info({"name": self.name})
		result = self._collection.find({"name": self.name})

		if result.count() > 1:
			raise Exception('Existe mas de un registro con el mismo nombre para objecto Workflow')

		if result.count() == 0:
			logging.info('no hay registros')
			return None

		return result[0]

	def serialize(self):

		act = {}

		for k in self._activities.keys():
			act.update({ k : self._activities[k].serialize() })
		att = self.serializeProperties()
		att.update({'activities': act })
		return att

	def load(self):
		result = self._collection.find_one({"_id": ObjectId(self._id)})	
		#print(result)
		if result is not None:
			self.name = result['name']
			self.descripcion = result['description']
			self.id = str(result['_id'])

			for key in result['activities'].keys():
				self._activities.update({ key: Activity(self, spec =result['activities'][key])})
		else:
			raise Exception("Object ID '" + self._id + "' doesn't exists")

	
	def __createInstanceStruct(self):
		self.instance_data = { "workflow_name": self.name,
				"state": self._state,
				"current_activity" : self._cursor,
				"global_variables" : self._global_vars,
				"stages" : None }
		stages = {}
		#logging.info(self._activities)
		for key , activity in self._activities.items():
			stages.update( { key : activity.serialize() } )
		self.instance_data['stages'] = stages

	def __getInitActivity(self):
		try:
			for key, activity in self._activities.items():			
				if activity.type == 'init':
					logging.debug('Actividad de inicio: ' + key )
					self.instance_data['current_activity'] = activity.name
					return activity.name
		except Exception as e:
			logging.error('Actividades: ', self._activities)
			logging.error('Instance Data: ',self.instance_data)
			raise e
	

	"""
	Este metodo inicia un workflow desde cero. Si el workflow es sincrono, entonces
	esta debería iniciar y terminar acá

	"""
	def execute(self,params = None):
		current = None
		#buscar la actividad de inicio
		"""
		Ejecucion de algo acá
		"""
		self._executed_stages = []
		nextActivity = None
		#En este punto ya esta cargada toda la estrucutra de clases.
		logging.info('BEGIN WORKFLOW ############################')
		self.__createInstanceStruct()  # Creando la estructura
		self.__persistWorkflowData()  # Esto se ejecuta una sola vez

		next_activity = self.__getInitActivity()  #solo buscando la actividad de inicio.  
		logging.debug('INICIO:' +next_activity)
		_input =  params 

		try:

			while True:   #Do 
				self._cursor = next_activity
				nextActivity = self._activities[next_activity]
				#Ejecución de la primera actividad
				result = nextActivity.execute(_input)
				self.__updateStage(activity_name = next_activity, 
					state = result['state'] , 
					_input = result['input'], 
					_output = result['output'])
				logging.debug(result)
				next_activity = result['next_activity']
				
				if result['state'] not in ['COMPLETED','FINISHED'] or next_activity == None :   #While
					break
				self.__persistWorflowState()
				_input = result['output']
			 
			if nextActivity.execution_type == "asynch":
				logging.info('WAITING FOR CALLBACK WORKFLOW ############################')
				self._cursor = nextActivity.name
				self._state = "WAITING_CALLBACK"
				return self.__registerCallback(nextActivity)
			
		except Exception as e:
		    logging.error(str(e))
		    raise e

		print('END WORKFLOW ############################')
		return self.instance_data['_id']

	def __persistWorflowState(self , state = None, cursor = None):

		self._cursor = cursor if cursor is not None else self._cursor
		self._state = state if state is not None else self._state

		wfid = str(self.instance_data['_id'])
		logging.info('Guardando estado del workflow: ' + wfid)
		data = { "state" : self._state}
		data.update({ "current_activity" : self._cursor })
		query = { '$set' : data }
		logging.debug('	Query:    ' + str(query) + " , ID: " + wfid)
		self._executions.update_one({ "_id" : ObjectId(wfid) } , query)



	def __registerCallback(self,activity):

		db_callbacks = self._mydb['callbacks']
		
		logging.info('REGISTER CALLBACK <-------')
		#actualizar workflow
		#self.__persistWorflowState( state = 'WAITING_CALLBACK', cursor =  activity.name )
		#actualizar actividad. Esto es provisorio, se determinara su utlizadad en el futuro
		activity.state = 'WAITING' 
		#data = activity.serialize()
		data = {}
		data.update({ "workflow_id" : self.instance_data['_id']})
		result = db_callbacks.insert_one(data)
		
		return {
			"execution_id": str(self.instance_data['_id']),
			"callback_id": str(result.inserted_id) 
		}

	"""
	Este metodo debe ser llamado cada vez que se requiera contnuar desde algún punto 
	"""
	def __continueFlow(self,transition_name,prev_output):
		
		logging.info('CONTINUE WORKFLOW ############################ '+ self.name + ",ACTIVITY:" + transition_name + ' ------->')
		logging.debug('INPUT: '+ str(prev_output))
		if transition_name != None:
			#Se supone que debería existir
			if transition_name not in self._activities:
				raise Exception(trs.next + ' No existe como actividad registrada')
			
			try:
				#Actualizar el puntero. Esto se ejecuta minumo una vez
				self._cursor = transition_name

				self.__persistWorflowState(cursor = transition_name, state = "RUNNING") 

				params = prev_output
				next_activity = transition_name
				activity = None
				while True:   #Do

					activity = self._activities[next_activity]  # Obtener actividad
					result = activity.execute(params)  # Ejecutar
					logging.info('====> EXECUTION RESULT: ',result)
					self.__updateStage(activity_name = activity.name, 
						state=result['state'],
						_input = result['input'],
						_output = result['output'])

					next_activity = result['next_activity']   #revisar la proxima actividad
					params = result['output']   #obtener los proximos input
					#Gutradar la ejecucion actual
					
					if result['state'] not in ['COMPLETED','FINISHED'] or next_activity == None:   #While
						break

					self.__persistWorflowState( state = "RUNNING", cursor = next_activity)
					self._cursor = next_activity

				if result['state'] == 'WAITING':
					self.__persistWorflowState( state = "WAITING", cursor = activity.name) #Aun es la misma

					return self. __registerCallback(activity)
				else:
				# fin o pausa del flujo
					self._state = "FINISHED"
					self.__persistWorflowState( state = "COMPLETED", cursor = '')
				

				return result
			except Exception as e:
				#Agregar qye el workflow esta en error
				self.state = "ERROR"
				self.__persistWorflowState(self , state = "ERROR", cursor = self._cursor)
				raise e			

		else:
			logging.info("WORKFLOW FINALIZADO")
 

	""" Este metodo continua una acción en espera de ejecución """
	def callback(self,id_callback ,response):
		logging.info('CALLBACK WORKFLOW ############################ '+ self.name + " Id: " + id_callback +' ------->')
		if id_callback == None:
			raise Exception('Se ha enviado un dato nulo')
		
		#recueprar la actividad pendiente
		data = self._callbacks.find_one({ "_id" : ObjectId(id_callback)})
		# logging.debug(str(data))
		self.__loadExecutionData(data['workflow_id'])
		
		#Actualizar el stage correspondiente.  Se cargo toda la data. Se debe obtener una actividad de la lista
		if data['name'] not in self._activities:
			raise Exception('El nombre ', data['name'], ' no existe en la lista')

		activity = self._activities[data['name']]
		activity.status = 'RUNNING'
		#actuakizar el estado del workflow antes de qhacer cualquier cosa
		self.__persistWorflowState(state = "RUNNING", cursor = activity.name )
		
		#Ejecucion de la actividad
		result = activity.callback(response)
		#actualizando el estado
		logging.debug(type(result))
		self.__updateStage(activity_name = activity.name, 
			_output = result['output'] , 
			_input  = result['input'], 
			state = 'COMPLETED')

		logging.info('result callback ', result)

		if result['next_activity'] != None:  #Entonces hay siguiente
			self._cursor = result['next_activity']
			self.__persistWorkflowData()
			result = self.__continueFlow(result['next_activity'],result['output'])
		else:
			logging.info('Finalizando Workflow -------------------> ')
			self.__persistWorflowState(state = "FINISHED")

		self.__persistWorkflowData()
		 #Guardar el estado actual del Workflow en este piunto
		#TODO Eliminar el registro de ejeución según su ID para que no se vuelva a usar.
		
		logging.info('FIN CALLBACK ############################ ' + self.name + " Id: " + id_callback +' ------->')
		return result
		
		
	
	def __updateStage(self, activity_name , state, _input = '', _output = ''):
		logging.info('Actualizando estado: '+ activity_name)
		_id = self.instance_data["_id"]

		#actualizar el stage en memoria
		activity = self._activities[activity_name]
		activity.state = state
		activity.input = _input
		activity.output = _output

		data = { "stages." + activity_name + ".status" : state  }
		data.update({ "stages." + activity_name + ".input" : _input })
		data.update({ "stages." + activity_name + ".output" : _output })

		query = { '$set' : data }

		retval = self._executions.update_one({ "_id" : ObjectId(_id)}, query)
		logging.debug(retval)
	"""
	Los datos ya deben existir antes de llamar a esta funcion
	"""
	def __persistWorkflowData(self):
		if self.instance_data == None:
			raise Exception('No se ha creado la estructura de ejeucion')
		if "_id" not in self.instance_data:
			#print('Creando nuevo registro de ejecucion')
			result = self._executions.insert_one(self.instance_data)  #Persistor el workflow
			self.instance_data.update({ "_id" : ObjectId(result.inserted_id)})

	"""
	Este metodo solo carga la información que ya existe en la base de datos.  

	"""
	def __loadExecutionData(self,_id ):
		logging.info('LOAD:  ExecucionData ')
		logging.debug(_id)
		try:
			self.instance_data = self._executions.find_one({"_id": ObjectId(_id)})
			if self.instance_data == None:
				raise Exception('_Id ' + _id + " doesn't exists on execution collection")

			self._cursor = self.instance_data['current_activity']
			
			for name in self.instance_data['stages']:

				activity = Activity(workflow = self, spec = self.instance_data['stages'][name])  
				self._activities[name] = activity

		except Exception as e:
			logging.error(e)
			logging.error('Instance data: ' +  str(self.instance_data))
			raise e


class Transition(BaseElement):
	fields = [ 'name', 'prev', 'next','condition']

	def __init__(self,spec):
		#TODO validar el contenido
		super(Transition,self).__init__(spec['name'])

		self.name = spec['name']
		self._condition = spec['condition'] if 'condition' in spec else None
		self._default = True #Valor por defecto

		self.prev = spec['prev'] if 'prev' in spec else None
		self.next = spec['next'] if 'next' in spec else None
 
	@property
	def condition(self):
		return self._condition

	@condition.setter
	def condition(self,cond):
		#validar la condicion
		self._condition = cond

	def validateConditions(self,cond):
		pass

	def setPrev(self,activity):
		self.prev = activity.name

	def setNext(self, activity):
		self.next = activity.name

	def serialize(self):
		att = self.serializeProperties()
		return att

	def save(self):
		pass

	@property
	def default(self):
		return self._default
	
	@default.setter
	def default(self,isdefault = True):
		self._default = isdefault


	def evaluate(self,_vars):
		if self._condition is None or self._condition == '':
			logging.debug('Salida directa:' + self.name + '->' + self.next)
			return True

		try:
			result = eval(self._condition,{'__builtins__': None}, _vars)
			logging.debug('CONDITION: ' + self._condition )
			logging.debug('RESULTADO: ' + str(result) )
			logging.debug('         -----------> ' + self.next)
			return result
		except Exception as e:
			logging.error('condition:', self._condition)
			logging.error('dump vars:',_vars)
			raise e


class Activity(BaseElement):

	_collection_name = 'workflows'

	_states = [ 'RUNNING', 'IDLE', 'STOPPED', 'FINISHED','WAITING', 'NO_EXECUTED','COMPLETED']

	@property
	def execution_type(self):
		return self._execution_type
	

	@execution_type.setter
	def execution_type(self,execution_type):
		if execution_type not in ['sync','async']:
			raise Exception('Execution type must be sync or async')
		self._execution_type = execution_type



	@property
	def output(self):
		return self._output
	
	@output.setter
	def output(self, output):
		self._output = output

	@property
	def input(self):
		return self._input
	

	@input.setter
	def input(self,inp):
		self._input = inp


	@property
	def type(self):
		return self._type


	@property
	def state(self):
		return self._state
	

	@state.setter
	def state(self, state):
		self._state = state

	def __init__(self,workflow, spec ):
		super(Activity,self).__init__(spec['name'])
		if not isinstance(workflow,Workflow):
			raise Exception('El parametro Workflow debe ser de tipo Workflow')

		self._specification = spec
		self._workflow = workflow
		self.name = spec['name']
		self._type = spec['type'] if 'type' in spec else None
		self._transitions = {}
		self._state = 'NO_EXECUTED' if 'status' not in spec else spec['status']
		self._input = "" if 'output' not in spec else spec['output']
		self._output = "" if 'input' not in spec else spec['input']
		self._execution_type  = 'sync' if 'execution_type' not in spec else spec['execution_type']
		self._callback = {}

		if self._execution_type == 'asynch':
			logging.debug('Cargando callbacks')
			self.__loadCallbackActions(spec)

		self._actions = spec['actions'] if 'actions' in spec else {}

		if self._type not in ["init","end","activity", None, ""]:
			logging.error(self._type)
			raise Exception('You must especify activity tpye ["init", "end" or none ]')

		#logging.debug('---> Cargando transiciones')
		self.__loadTransitions(spec)

		workflow.addActivity(self)

	def __loadTransitions(self,spec ):
		if 'transitions' in spec:
			transitions_list = spec['transitions'] #esto es una lista
			
			if isinstance(transitions_list,dict):
				for name, transition in transitions_list.items():
					self._transitions.update({ name : Transition(spec=transition) })
			else:
				for transition in transitions_list:	
					self._transitions.update({ transition['name'] : Transition(spec=transition) })

	def __loadCallbackActions(self,spec):
		logging.debug('__loadCallbackActions: ' + str(spec['callback']))
		if 'callback' not in spec:
			raise Exception('This Activity was defined like a Asynch and there is not "callback" section on especification',spec)
		self._callback = spec['callback']


	def __dbBootstrap(self):
		col = self._mydb.list_collection_names()
		if self._collection_name not in col:
			print('Bootstrap: Workflow')
			#Crear la collection
			self._collection = self._mydb[self._collection_name] 
			print('collection workflows created')
			self._collection.create_index(
				[('activities', pymongo.ASCENDING)],  name='activity_index', default_language='english')

	def serialize(self):
		try:
			trs = {}
			#print('serializando transicion')
			for t in self._transitions:
				trs.update( {  t : self._transitions[t].serialize() } )

			return { 'name': self.name, 
			'type': self._type if self._type != None else ''  , 
			'execution_type': self._execution_type , 
			'transitions' : trs,
			'status': self._state,
			'actions': self._actions,
			'input': self.input ,
			'output': self.output,
			'callback': self._callback }
		except Exception as e:
			print('Especification:', self._specification)
			raise e

	"""
	
	"""
	@property
	def transitions(self):
		return self._transitions


	def addTransition(self,transition,sense = 'next'):

		if sense not in ['next', 'prev' ] or sense == None:
			raise Exception('Sense of transition must be next o prev')

		if not isinstance(transition,Transition):
			raise Exception("The object must be 'Transition' class")

		if sense == 'next':
			transition.setNext(self)
		else:
			transition.setPrev(self)

		if transition.name not in self._transitions.keys():
			self._transitions.update({ transition.name : transition } )
		else:
			raise Exception("The transition name '" + transition.name + " is already registered on this activity")

	def __evaluateTransition(self,params):
		#print('transitions',self._transitions)
		if params == None:
			raise Exception('Parametros nulos')

		if len(self._transitions) == 0:
			logging.debug('No hay transiciones de salida')
			return None

		for k in self._transitions.keys():
			trs = self._transitions[k]
			if trs.evaluate(params):
				logging.debug('SALIDA: ' + trs.next)
				return trs

		#si pasa a este punto es por que no se encontró ninguna 
		#Buscar la salida por defecto
		for k, item in self._transitions.items():
			#print(k,item)
			if item.default:
				return item


	def __saveState(self,input,output):

		data = { "name"   : self.name,
		         "input"  : input if input != None else '',
		         "output" : output if output != None else '',
		         "estado" : self._state  }
		return data


	def __call(self,_input, input_actions = None):
		af = ActionFactory()
		result = {}
		input_vars = _input
		output_vars = {}

		#Prioridad por sobre las acciones locales.
		actions = self._actions if input_actions == None else input_actions
		
		for action in actions:
			
			_oper = action['operation'] if 'operation' in action else None
			_in = action['input'] if 'input' in action else None
			#print('entrada',_in)
			_out = action['output'] if 'output' in action else None


			action = af.load(action['type'])
			if action != None:
				output = action.execute(inputs=input_vars,select = _in, operation = _oper, output = _out )
				output_vars.update(output)  #la salida debe ser la entrada de la siguiente
				input_vars.update(output)
				

		return { "state": "WAITING" if self._execution_type == 'asynch' else 'COMPLETED',
		 		"output" : output_vars ,
		 		"input" : _input }  #Esperando por una respuesta.  Si es asícrono
	
	"""	                               
	Si es finished, es por que es proceso sincrono, por lo tanto va al siguiente
	Si es una actividad de fin, entonces va al estado finishe
    """
	def execute(self,params, vars = None ):
		#el resultado de eta actividad es la entrada de los params
		logging.info('ACTIVITY EXECUTING -------| '+self.name)
		#Ejecutar algo acá
		self._state = 'RUNNING'
		output = self.__call(params)
		self.input = params
		
		if output['state']  == 'COMPLETED':
			#Esto significa que la actividad se ejecutó completamente (Sync)
			#opciones:  Sigue una nueva transición, o podría no tener niguna
			logging.debug('Resultado ejecucion; '+ str(output))
			#self.__registerVariables(params)
			transition = self.__evaluateTransition(params)
			logging.debug('validando Salida')
			self._state = 'COMPLETED' if transition != None else 'FINISHED'
			self.output = output['output']
			return { "state" :  self._state, 
					"next_activity" : transition.next if transition != None else None , 
					"output": output['output'],
					"input" : params }

		self._output = output['output']

		if output['state'] == 'WAITING':  #Esperando por una respuesta
			self._state = 'WAITING'
			return { "state" :  'WAITING', 
					"next_activity" : None ,  
					"output": output['output'],
					"input": params }


	def callback(self, response):
		logging.info('<---------   Callback ' + self.name + ' ' + self.state + '  ----------->')
		logging.info('INPUT: ' + str(response))
		#print(self.serialize())
		if self.state == 'WAITING':
			
			result = self.__call(response,input_actions=self._callback['actions'])

			transition = self.__evaluateTransition(response)
			logging.info('Callback result; ' + str(result))
			self._output = result['output']
			self._state = 'COMPLETED'

			return self.__createExcutionReturn(transition.next if transition is not None else None)
		else:
			logging.info(self.serialize())
			raise Exception('Wrong state to call Activity. State: ' + self.state)


	def __createExcutionReturn(self,next_activity = None):
		return { "state" :  self._state, 
					"next_activity" : next_activity ,  
					"output": self._output,
					"input": self._input }
	



