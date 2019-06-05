
class Action(object):

	def __init__(self):
		pass 
	""" de las variables de entrada, sleeccionar, y ejecutar esta operacion """
	def execute(self,inputs = None, params = None, operation = None ):
		print(inputs)
		print(params)
		print(operation)
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

	# def execute(self,inputs = None, params = None, operation = None ):
	# 	print(self._msg)

class Evaluate(Action):

	def __init__(self):
		pass

	def execute(self,inputs = None, params = None, operation = None ):
		#if params != None and operation != None:
		print('#############################',operation)

		retval = eval(operation,{'__builtins__': None},inputs)
		print(retval)
		return retval
		#if params != None:


class ActionFactory(object):

	def __init__(self):
		pass

	def load(self,action):
		print('####', action)
		if action == 'CallLambda':
			return CallLamda()
		if action == 'Webhook':
			return Webhook()
		if action == 'Evaluate':
			return Evaluate()


