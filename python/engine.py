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
	struct = None
	#Attributos serializable
	fields = ['id','name','description','cursor']  #xw

	@property
	def cursor(self):
		return self._cursor

	@property
	def activities(self):
		return self._activities


	def __init__(self, spec):
		super(Workflow,self).__init__(spec['name'])
		self.id = None
		self._activities = {}
		self._collection = None
		self._cursor = None
		self._id = spec['_id'] if '_id' in spec else None
		#
		if self._id != None:
			self.load()
		else:
			self.name = spec['name']
			self.description = spec['description']
			self._collection_name = 'workflows'
			self._collection = self._mydb[self._collection_name] 
			self.__loadFromJSON(spec)

	def __loadFromJSON(self, spec):
		print(self._activities)
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
		result = self._collection.find({"_id": ObjectId(self._id)})	
		if result.count() > 0:
			r = result[0]
			self.name = r['name']
			self.descripcion = r['description']
			self.id = str(r['_id'])

			#Inicializar el array de activities
			for key in r['activities'].keys():
				print('---> cargando actividad: ' + key)
				print(r['activities'][key])
				self._activities.update({ key: Activity(self,name = key,  _json=r['activities'][key])})
		else:
			raise Exception("Object ID '" + self._id + "' doesn't exists")

	def save(self):
		try:
			print("--------")
			print(self.serialize())
		
			encoded_object = self.serialize()  #ast.literal_eval(str(self.serialize()))
			record = None
			if self._id != None:
				#Actualizar registro
				print('actualizando registro')
				record = self._collection.update_one({"_id" : ObjectId(self.id) }, { '$set' : encoded_object })
				print()
				return self.id
			else:
				record = self._collection.insert_one(encoded_object)
				return record.inserted_id
			print('registro guardado')
		except DuplicateKeyError:
			print('Duplicate record Name ' + self.name)


	def execute(self,params = None):
		for activity in self._activities:
			print('Ejecutando actividad: ' + activity )
			self._activities[activity].execute(params)


class Transition(BaseElement):
	fields = [ 'name', 'prev', 'next']

	def __init__(self,spec):
		#TODO validar el contenido
		super(Transition,self).__init__(spec['name'])
		#print('--------> Transition ' + self.name)
		# if (prev != None and not isinstance(prev,Activity)) or (next != None and not isinstance(next,Activity)):
		# 	raise Exception("Parameters 'next' and 'prev' must be an Activity class instance")
		self.name = spec['name']
		self._condition = spec['condition']

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

	def evaluate(self,params):
		print('Evaluando transicion: ' + self._condition)
		print(eval(self._condition,params))
		result = eval(self._condition,params)
		return result

	

class Activity(BaseElement):

	_collection_name = 'workflows'

	_states = [ 'EXECUTING', 'IDLE', 'STOPPED', 'FINISHED','WAITING', 'NO_EXECUTED']

	#_transitions = {}

	_state = 'NO_EXECUTED'

	@property
	def type(self):
		return self._type


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

		if self._type not in ["init","end", None]:
			raise Exception('You must especify activity tpye ["init", "end" or none ]')

		if 'transitions' in spec:
			#print('Procesando transitions')
			#print('->',spec['transitions'])

			for tr in spec['transitions']:	
				self._transitions.update({ tr['name'] : Transition(spec=tr) })
			
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
		for k in self._transitions.keys():
			if self._transitions[k].evaluate(params):
				print('La salida es: ' + k)
				return self._transitions[k]
		else:
			#retornar la salida por defecto
			print(self._transitions)
			if len(self._transitions) > 0:
				idx = self._transitions.keys()[0]
				return self._transitions[idx]

	def execute(self,params):
		#Ejecutar algo acá
		#el resultado de eta actividad es la entrada de los params

		transition = self.__evaluateTransition(params)
		if transition == None:
			#esto es finalizar
			self._state = 'FINISHED'
			return
		#Hacer algo, y finalizar

		if isinstance(transition,Transition):
			print('revisando cual es el siguiente paso')
			print(transition.next)
			self._state = 'FINISHED'
		else:
			print('xxx')


