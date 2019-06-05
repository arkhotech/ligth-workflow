import sys
import inspect
import json
import ast
import logging

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

	_collection_name = 'workflows'

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
		self._global_vars = {}
		self._cursor = None
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
			print('Bootstrap: Workflow')
			#Crear la collection
			self._collection = self._mydb[self._collection_name] 
			print('collection workflows created')
			self._collection.create_index(
				[('name', pymongo.ASCENDING)], unique=True, name='workflow_index', default_language='english')

	def __existsName(self):
		print({"name": self.name})
		result = self._collection.find({"name": self.name})

		if result.count() > 1:
			raise Exception('Existe mas de un registro con el mismo nombre para objecto Workflow')

		if result.count() == 0:
			print('no hay registros')
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

			#Inicializar el array de activities
			for key in result['activities'].keys():
				#print('---> cargando actividad: ' + key)
				#print(result['activities'][key])
				self._activities.update({ key: Activity(self, spec =result['activities'][key])})
		else:
			raise Exception("Object ID '" + self._id + "' doesn't exists")

	def save(self):
		try:
			print("--------")
			#print(self.serialize())
		
			encoded_object = self.serialize()  #ast.literal_eval(str(self.serialize()))
			record = None
			if self._id != None:
				#Actualizar registro
				print('actualizando registro')
				record = self._collection.update_one({"_id" : ObjectId(self.id) }, { '$set' : encoded_object })
				
				return self.id
			else:
				record = self._collection.insert_one(encoded_object)
				return record.inserted_id
			print('registro guardado')
		except DuplicateKeyError:
			print('Duplicate record Name ' + self.name)

	
	def __createInstanceStruct(self):
		self.instance_data = { "workflow_name": self.name,
				"state": "running",
				"current_activity" : self._cursor,
				"global_variables" : self._global_vars,
				"stages" : {} }

	def __getInitActivity(self):
		try:
			for key, activity in self._activities.items():			
				if activity.type == 'init':
					print('Actividad de inicio: ' + key )
					self.instance_data['current_activity'] = activity.name
					return activity
		except Exception as e:
			print('Actividades: ', self._activities)
			print('Instance Data: ',self.instance_data)
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
		stages = []
		nextActivity = None

		print('BEGIN WORKFLOW ############################')
		self.__createInstanceStruct()
		nextActivity = self.__getInitActivity()
		print('INICIO:',nextActivity.name)
		try:
			output = nextActivity.execute(params)
			print('OUTPUT:',output)
			self.__persistWorkflowData(params)

			stages.append(self.__createRecord(nextActivity.name,output))

			while output['state'] in ['COMPLETED','FINISHED']:
				#buscar las siguientes transiciones
				next_activity = output['next_activity']
				print('NEXT ACTIVITY: ',next_activity)
				if next_activity != None:
					current = self._activities[next_activity]
					self.instance_data['current_activity'] = next_activity
					output  = current.execute(params)
					stages.append(self.__createRecord(next_activity,output))
				else:
					break

		except Exception as e:
		    print(str(e))
		    raise e

		#print('excute: ',self.instance_data)
		self.instance_data['stages'] = stages
		#print('excute2: ',self.instance_data)
		self._state = "FINISHED"
		self.__persistWorkflowData()
		print('END WORKFLOW ############################')
		return self.instance_data['_id']


	def __continueFlow(self,transition):
		#print(transition.next)
		if transition != None:
			#Se supone que debería existir
			if transition.next not in self._activities:
				raise Exception(trs.next + ' No existe como actividad registrada')
			self._cursor = transition.next
			result = self._activities[self._cursor].execute(self._global_vars)
			self.instance_data['stages'].append(self.__createRecord(self._cursor,result))
			#Falta registrar el resultado
			self.instance_data['current_activity'] = self._cursor
			self.__persistWorkflowData()
			#print('---> ', result)
			while result['state'] in ['COMPLETED','FINISHED']:
				next_activity = result['next_activity']
				if next_activity != None:
					current = self._activities[next_activity]
					self.instance_data['current_activity'] = next_activity
					result  = current.execute(params)
					self.instance_data['current_activity'].append(self.__createRecord(next_activity,result))					
					self.__persistWorkflowData()
				else:
					print('RESULT: ',result['state'])
					break
			print('Fin del loop')
		else:
			print("WORKFLOW FINALIZADO")

	def callback(self,execution,response):
		print('<-----  Workflow Callback: ', self.name ,' ------->')
		print(execution)
		if execution == None:
			raise Exception('Se ha enviado un dato nulo')
		

		self.__loadExecutionData(execution["_id"])

		current_activity_name = execution['current_activity']
		print('stage -->',current_activity_name)
		current = self._activities[current_activity_name]
		if current == None:
			raise Exception('No se encuentra la actividad dentro de la lista')
		trs = current.callback(response)  #Continuar el proceso pendiente
		self.__continueFlow(trs)
		
		self.__persistWorkflowData()
	
	def __createRecord(self,act_name,output):
		return {
				"activity" : act_name,
				"status"   : output['state'],
				"output"   : output 
		}

	def __persistWorkflowData(self,input_params=None):

		if self.instance_data == None:
			self.instance_data = { "workflow_name": self.name,"state": "running",
				"current_activity" : self._cursor,
				"global_variables" : self._global_vars,
				"stages" : {} }

		if input_params != None:
			self.instance_data.update({ "input": input_params }) 
		#Persistir el estado de ejecución. Si tiene ID se actualiza, si no
		#print('__persistWorkflowData:',self.instance_data)
		if "_id" not in self.instance_data:
			print('Creando nuevo registro de ejecucion')
			result = self._executions.insert_one(self.instance_data)  #Persistor el workflow
			self.instance_data.update({ "_id" : ObjectId(result.inserted_id)})
		else:
			print('actualizando registro de ejecucion')
			self._executions.update({"_id" : self.instance_data["_id"]},self.instance_data)
		#print('out: __persistWorkflowData:',self.instance_data)

	def __loadExecutionData(self,_id ):
		print('LOAD:  ExecucionData')
		try:
			self.instance_data = self._executions.find_one({"_id": ObjectId(_id)})
			if self.instance_data == None:
				raise Exception('_Id ' + _id + " doesn't exists on execution collection")
			self._cursor = self.instance_data['current_activity']
			for stage in self.instance_data['stages']:
				print('actualizando staage: ', stage['activity'])
				activity = self._activities[stage['activity']]
				activity.output = stage['output']
				activity.state = stage['status']
				#activity.input = stage['input'] 
		except Exception as e:
			print('Instance dta: ', self.instance_data)
			raise e


class Transition(BaseElement):
	fields = [ 'name', 'prev', 'next','condition']

	def __init__(self,spec):
		#TODO validar el contenido
		super(Transition,self).__init__(spec['name'])
		#print('--------> Transition ' + self.name)
		# if (prev != None and not isinstance(prev,Activity)) or (next != None and not isinstance(next,Activity)):
		# 	raise Exception("Parameters 'next' and 'prev' must be an Activity class instance")
		#print(spec)
		self.name = spec['name']
		self._condition = spec['condition']
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
		print('Evaluando transicion: ' + self.name + ' -> condition = ' + self._condition, 'Params: ', _vars)
		#print('Resultado: ',eval(self._condition,params))
		result = eval(self._condition,{'__builtins__': None}, _vars)
		print(result)
		return result


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

		#print('-----> Activity: ' + spec['name'] )
		#print('-----> spec ', spec)
		self._workflow = workflow
		self.name = spec['name']
		self._type = spec['type']
		self._transitions = {}
		self._state = 'NO_EXECUTED'
		self._execution_type  = 'sync' if 'execution_type' not in spec else spec['execution_type']
		self._actions = spec['actions'] if 'actions' in spec else {}

		if self._type not in ["init","end","activity", None]:
			raise Exception('You must especify activity tpye ["init", "end" or none ]')

		if 'transitions' in spec:
			transitions_list = spec['transitions'] #esto es una lista
			print(transitions_list)
			for transition in transitions_list:	
				print('-->',transition)
				self._transitions.update({ transition['name'] : Transition(spec=transition) })
			
		#print(self.name, self._transitions)
		#print('<-----------------------')

		workflow.addActivity(self)

		

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
		trs = {}
		print('serializando transicion')
		for t in self._transitions:
			trs.update( {  t : self._transitions[t].serialize() } )

		return { 'name': self.name, 
		'type': self._type, 
		'execution_type': self._execution_type , 
		'transitions' : trs,
		'actions': self._actions }

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

		if params == None:
			raise Exception('Parametros nulos')

		if len(self._transitions) == 0:
			return None

		for k in self._transitions.keys():
			trs = self._transitions[k]
			#print(params)
			if trs.evaluate(params):
				return trs

		#si pasa a este punto es por que no se encontró ninguna 
		#Buscar la salida por defecto
		for k, item in self._transitions.items():
			print(k,item)
			if item.default:
				return item


	def __saveState(self,input,output):

		data = { "name"   : self.name,
		         "input"  : input if input != None else '',
		         "output" : output if output != None else '',
		         "estado" : self._state  }
		return data

	def call(self,_input):
		af = ActionFactory()
		result = {}
		for action in self._actions:
			print('Ejecutando action:',action)
			_oper = action['operation'] if 'operation' in action else None
			_in = action['input'] if 'input' in action else None
			_out = action['output'] if 'output' in action else None

			action = af.load(action['type'])
			if action != None:
				output = action.execute(inputs=_input,params = _in, operation = _oper )
			result.update()

		return { "state": "WAITING" if self._execution_type == 'async' else 'COMPLETED',
		 		"output" : result }  #Esperando por una respuesta.  Si es asícrono
	"""	                               
	Si es finished, es por que es proceso sincrono, por lo tanto va al siguiente
	Si es una actividad de fin, entonces va al estado finishe
    """


	def execute(self,params, vars = None ):
		#el resultado de eta actividad es la entrada de los params
		print('ACTIVITY EXECUTING -------|',self.name)
		#Ejecutar algo acá
		output = self.call(params)
		print('output -> ',output)
		if output['state']  == 'COMPLETED':
			#Esto significa que la actividad se ejecutó completamente (Sync)
			#opciones:  Sigue una nueva transición, o podría no tener niguna
			self.__registerVariables(params)
			transition = self.__evaluateTransition(params)

			self._state = 'COMPLETED' if transition != None else 'FINISHED'
			return { "state" :  self._state, 
					"next_activity" : transition.next if transition != None else None , 
					"output": output['output'] }
		
		if output['state'] == 'WAITING':  #Esperando por una respuesta

			return { "state" :  'WAITING', 
					"next_activity" : None , 
					"output": output['output'] }


	def callback(self, response):
		print('<---------   Callback ',self.name,self.state,'  ----------->')
		if self.state == 'WAITING':
			print('Continuando proceso', self.name)
			"""	
			Ejecutar algo con la respuesta y obtener el siguiente 
			"""
			nextActivity = self.__evaluateTransition(response)
			self._output = { "estado" : "terminado"}
			self._state = 'COMPLETED'
			return nextActivity	


	def __registerVariables(self,variables):
		print('registrando variables')
		if not isinstance(variables,dict):
		 	raise Exception('las variabels deben ser de tipo dict')
		for var in variables:
			print(var)
			#self._workflow.global_vars[key] = value
	



