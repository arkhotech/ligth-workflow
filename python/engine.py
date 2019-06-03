import sys
import inspect
import json
import ast
import logging


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
	fields = ['id','name','description','cursor']  #xw

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
		self._exec_coll = self._mydb['executions']
		self._global_vars = {}
		self._cursor = None
		self._id = spec['_id'] if '_id' in spec else None
		self.instance_data = {}
		#


		self._exec_coll = self._mydb['executions']
		if self._id != None:
			self.load()
		else:
			self.instance_data = { "workflow_name": self.name, 
				"input": params, 
				"state": "running" , 
				"current_activity" : "",
				"stages" : {} }
			
			self.description = spec['description']
			
			
			self.__loadFromJSON(spec)

	def __loadFromJSON(self, spec):
		#print(self._activities)
		act = spec['activities']
		for k in act.keys():
			act[k].update({"name": k})
			self._activities.update({ k : Activity(workflow=self,spec=act[k])})

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
				print('---> cargando actividad: ' + key)
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


	def __updateActotyStates(self,stages):
		print('update -->' ,stages)
		for stage in stages:
			print('Item: ',stage)
			activity = self._activities[stage['activity']]
			activity.state = stage['status']
			activity.output = stage['output']

	

	def __getInitActivity(self):
		for activity in self._activities:			
			current = self._activities[activity]
			if current.type == 'init':
				print('Actividad de inicio: ' + activity )
				self.instance_data['current_activity'] = nextActivity.name
				return current

	"""
	Esta accion debería ejecutarse paso a paso.

	"""
	def execute(self,params = None):
		current = None
		#buscar la actividad de inicio
		"""
		Ejecucion de algo acá
		"""
		
		#crear registro
		result = self._exec_coll.insert_one(self.instance_data)  #Persistor el workflow
		record_id = result.inserted_id

		stages = []
		nextActivity = None
		if self._cursor == None:
			print('BEGIN WORKFLOW ############################')
			nextActivity = self.getInitActivity()
		else:
			print('CONTINUE WORKFLOW ',self._cursor, ' ############################')
			nextActivity = self._activities[self._cursor]

		try:
			#print('guardando el ID')
			#self._exec_coll.insert_one({ "workflow_name": self.name, "state": "running" })
			output = nextActivity.execute(params)
			#registro

			stages.append(self.__createRecord(nextActivity.name,output))

			#print(output)
			while output['state'] in ['COMPLETED','FINISHED']:
				#buscar las siguientes transiciones
				next_activity = output['next_activity']
				if next_activity != None:
					current = self._activities[next_activity]
					self.instance_data['current_activity'] = next_activity
					output  = current.execute(params)
					stages.append(self.__createRecord(next_activity,output))
				else:
					break

		except Exception as e:
		    print(str(e))

		self.instance_data['stages'] = stages
		print(self.instance_data)
		self._exec_coll.update({"_id": ObjectId(record_id)},self.instance_data)
		print('END WORKFLOW ############################')
		return record_id

	def callback(self,execution,response):
		print('<-----  Workflow Callback: ', self.name ,' ------->')
		print(execution)
		if execution == None:
			raise Exception('Se ha enviado un dato nulo')
		

		self.__updateActotyStates(execution['stages'])

		current_activity_name = execution['current_activity']
		print('stage -->',current_activity_name)
		current = self._activities[current_activity_name]
		if current == None:
			raise Exception('No se encuentra la actividad dentro de la lista')

		trs = current.callback(response)
		if trs != None:
			#Se supone que debería existir
			if trs.next not in self._activities:
				raise Exception(trs.next + ' No existe como actividad registrada')
			self._cursor = trs.next
			self.execute(self._global_vars)
		
		
		# for item in execution['stages'].items()
		# 	print(item)
		
	
	def __createRecord(self,act_name,output):
		return {
				"activity" : act_name,
				"status"   : output['state'],
				"output"   : output 
		}

	def update_states(self,execution):
		"""
		{ "_id" : ObjectId("5cf195fbc7a52459dbcd6f98"), 
		"workflow_name" : "test",
		 "input" : { "params" : 1 }, 
		 "state" : "running", 
		 "current_activity" : "A1", 
		 "stages" : [ { "activity" : "A1", "status" : "WAITING", "output" : { "state" : "WAITING", "next_activity" : null, "output" : { "porcentaje" : 10 } } } ] }
		"""
		self._cursor = execution['current_activity']
		for stage in execution['stages']:
			print('Updating  ----> ', stage)
			activity = self._activities[stage['activity']]
			activity._state = stage['status']




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

	#_transitions = {}

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

		if self._type not in ["init","end","activity", None]:
			raise Exception('You must especify activity tpye ["init", "end" or none ]')

		if 'transitions' in spec:
			trs = spec['transitions']
			for k in trs:	
				print('-->',trs[k])
				self._transitions.update({ trs[k]['name'] : Transition(spec=trs[k]) })
			
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
		#print(self._transitions)
		for t in self._transitions:
			trs.update( {  t : self._transitions[t].serialize() } )
			#print(trs)
		#print('--------------------------------------------------')

		return { 'name': self.name, 'type': self._type , 'transitions' : trs }

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

		return { "state": "WAITING" , "output" : { "porcentaje" : 10 }}  #Esperando por una respuesta.  Si es asícrono
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
					"next_activity" : transition.next , 
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
		if not isinstance(variables,'dict'):
			raise Exception('las variabels deben ser de tipo dict')
		for key,value in variables.item():
			self._workflow.global_vars[key] = value
	



