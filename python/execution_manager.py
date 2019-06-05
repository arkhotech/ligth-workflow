import sys
import inspect
import json
import ast
import logging

sys.path.append("./lib/python3.7/site-packages")

import pymongo
from bson import ObjectId
from pymongo.errors import *



class ExecutionManager(object):

	def __init__(self):
		self._client = pymongo.MongoClient("mongodb://localhost:27017/")
		self._mydb = self._client["wf-engine"] 
		self.collect = self._mydb['executions']


	def execute(self,params):
		_id = params['execution_id']
		cursor = self.collect.find_one({ "_id" : ObjectId( _id )})
		print(cursor)




e = ExecutionManager()

e.execute({"execution_id": "5ceea0f7a924c62999ffd519"})