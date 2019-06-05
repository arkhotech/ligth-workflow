import sys
import inspect
import json
import ast
import logging

sys.path.append("./lib/python3.7/site-packages")

import pymongo
from bson import ObjectId
from pymongo.errors import *
from engine import Workflow

class Api(object):


	def __init__(self):
		self._client = pymongo.MongoClient("mongodb://localhost:27017/")
		self._mydb = self._client["wf-engine"] 
		self.workflows = self._mydb['workflows']
		self.executions = self._mydb['executions']

	def listWorkflows(self):
		lista = self.workflows.find()
		print('ID','NOMBRE')
		for item in lista:
			print(item['_id'],item['name'])

	def executeWorkflow(self,id,input):
		wf = Workflow(spec={ "_id": id } )
		return wf.execute(input)

	def callbackWorkflow(self, _id, response ):
		execution = self.executions.find_one({"_id": ObjectId(_id)})
		print('callbackWorkflows: ', execution)
		if execution != None:
			#Get Workflow
			print('Buscando ', execution['workflow_name'])
			#Se carga la especificacion.
			wf_json = self.workflows.find_one({ "name" : execution['workflow_name']})
			#Actualizar los estados
			wf = Workflow(spec=wf_json)
			#wf.update_states(execution)
			wf.callback(execution,response)

	def continueWorkflow(self, _id, params = {}):
		execution = self.executions.find_one({"_id": ObjectId(_id)})
		if execution != None:
			#Get Workflow
			print('Buscando ', execution['workflow_name'])
			#Se carga la especificacion.
			wf_json = self.workflows.find_one({ "name" : execution['workflow_name']})
			#Actualizar los estados
			wf = Workflow(spec=wf_json)
			wf.execute(params, _id )


api = Api()

"Recuperar el Workflow  e iniciarlo"

idr = api.executeWorkflow("5ceece902db07216bcc3642a",{ "params": 1})#
print(idr)

api.callbackWorkflow(idr, { "response": { "status" : "rejected" }, "params": 5})

api.callbackWorkflow(idr, { "response": { "status" : "acepted" }, "params": 1})

api.callbackWorkflow(idr, { "response": { "status" : "acepted" }, "params": 1})
#api.continueWorkflow(_id = "5cf5524c5e8d2eaf0694bd2f")

#{ "_id" : "5ceecd8a18b32190ede09188", "cursor" : "", "description" : "test", "id" : "", "name" : "test", "activities" : { "A1" : { "name" : "A1", "type" : "init", "transitions" : { "t1" : { "name" : "t1", "next" : "B1", "prev" : "" }, "t2" : { "name" : "t2", "next" : "B2", "prev" : "" } } }, "B1" : { "name" : "B1", "type" : "end", "transitions" : { "tb1" : { "name" : "tb1", "next" : "F1", "prev" : "" }, "tb2" : { "name" : "tb2", "next" : "F2", "prev" : "" } } }, "B2" : { "name" : "B2", "type" : "activity", "transitions" : {  } }, "F1" : { "name" : "F1", "type" : "end", "transitions" : {  } }, "F2" : { "name" : "F2", "type" : "end", "transitions" : {  } } } }