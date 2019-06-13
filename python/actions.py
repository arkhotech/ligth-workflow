import logging


class Action(object):

	def __init__(self):
		pass 
	""" de las variables de entrada, sleeccionar, y ejecutar esta operacion """
	def execute(self,inputs = None, params = None, operation = None , output_name = None):
		logging.debug(inputs)
		logging.debug(params)
		logging.debug(operation)
		return { "result" : "ok" }


class CallLamda(Action):

	def __init__(self):
		print('init')
		self._msg ='Ejecutando CAllLambda'

	# def execute(self,inputs = None, params = None, operation = None ):
	# 	print(self._msg)


class Webhook(Action):
	
	def __init__(self):
		print('init')
		self._msg ='Ejecutando Webhook'

	def execute(self,inputs = None, params = None, operation = None, output_name = None ):
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

		

	def execute(self,inputs = None, params = None, operation = None, output_name = None ):
		#if params != None and operation != None:

		try:
			variables = {}
		
			if 'select' in params:
				variables = self.select(select = params['select'], _input = inputs)
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


