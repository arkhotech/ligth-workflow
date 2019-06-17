import logging
import boto3
import json

class Action(object):

	def __init__(self):
		pass 
	""" de las variables de entrada, sleeccionar, y ejecutar esta operacion """
	def execute(self,inputs = None, select = None, operation = None , output = None):
		logging.debug(inputs)
		logging.debug(select)
		logging.debug(operation)
		return { "result" : "ok" }


class CallLamda(Action):

	def __init__(self):
		print('init')
		self._client = boto3.client('lambda')
		self._msg ='Ejecutando CallLambda'

	def __entry__(self):
		return self

	def __exit__(self, type, value, tb):
		
		pass

	def __processOutput(self, select,  values):
		#tuplas selec
		retval = {}
		logging.debug(select)
		logging.debug(values)
		for to_var, from_var in select.items():
			logging.debug(to_var +" -> " + from_var)
			retval.update({to_var : values[from_var]})
		return retval


	def execute(self,inputs = None, select = None, operation = None, output = None ):
		logging.info('Llamando a un funcion: ' + operation)

		response = self._client.invoke(
		    FunctionName= operation,
		    InvocationType='RequestResponse',
		    Payload=json.dumps(inputs)
		)
		payload = response['Payload']
		data = payload.read().decode('utf8')
		retval = json.loads(data)
		logging.debug(response['StatusCode'])
		logging.debug('-----------------------')
		logging.debug(retval)
		logging.debug('-----------------------')

		if 'select' in output:
			return self.__processOutput(output['select'],retval)
		else:
			return { output : retval }

		
	def invoke():
		pass


class Webhook(Action):
	
	def __init__(self):
		print('init')
		self._msg ='Ejecutando Webhook'

	def execute(self,inputs = None, select = None, operation = None, output = None ):
		logging.info('Llamando a un hook')
		return { "result" : ""}

class Evaluate(Action):

	def __init__(self):
		pass

	"""
	
	"""
	def __enter__(self):
		return self

	def __exit__(self, type, value, tb):
		pass


	def select(self, select, _input):
		result = {}
		for key, value in select.items():
			if value not in _input:

				raise Exception('Key "' + key + '" doesn\'t exist on list of input parameters. Select: ',select,'Input:',_input)
			result.update({ key :  _input[value]})
		return result

		

	def execute(self,inputs = None, select = None, operation = None, output = None):
		#if params != None and operation != None:

		try:
			variables = {}
		
			if 'select' in select:
				variables = self.select(select = select['select'], _input = inputs)
			else:
				#print("====!",type(input))
				variables = inputs

			output_name = None if output_name == None else output_name
			logging.debug('-----> Procesando var  :' + str(variables))
			logging.debug('-----> Operation       :' + operation )
			retval = eval(operation,{'__builtins__': None},variables)
			logging.debug('-----> Result          : ' +str(retval))
			return {  output_name : retval } 
		
		except Exception as e:
			raise e

class ActionFactory(object):

	def __init__(self):
		pass

	def load(self,action):
		logging.debug('####' +str( action))
		if action == 'CallLambda':
			return CallLamda()
		if action == 'Webhook':
			return Webhook()
		if action == 'Evaluate':
			return Evaluate()


