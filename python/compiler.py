import sys
sys.path.append("./lib/python3.7/site-packages")

import yaml
from engine import Workflow, Activity, Transition
import json


class Test:

	def __enter__(self, type, value, tb):
		print('iniciando transaccion')

	def __exit__(self, type, value, tb):
		priint('Terminando transaccion')

	def execute(self):
		print('Ejecutando')

#Para reemplazar los valores None por null, Al parecer no funciona
def represent_none(self, _):
    return self.represent_scalar('tag:yaml.org,2002:null', '')

yaml.add_representer(type(None), represent_none)

#abrir el YAML y pasarlo a JSON
with open("asynch-test.yaml",'r') as stream:
	try:
		inp = json.dumps(yaml.safe_load(stream))
		spec = json.loads(inp)
		workflows = spec['workflows']

		for wf in workflows:
			w = Workflow(spec=wf)
			#w.save()
			print('Ejecutando')
			#print(w.serialize())
			#w.execute({"params": 60, "porcentaje": 50})
			result = w.execute({ "porcentaje": 25 , "monto":  100000 })
			result = w.callback( id_callback = result['callback_id'] ,response={"result": 300000, "iva": 20 })
			#result = w.callback( id_callback = result['callback_id'],response={"result": 300000, "iva": 20 })
			#result = w.callback( id_callback = result['callback_id'],response={"payload": 30 })
			print(result)
	
			t = Test()
			with t as test:
				test.execute()

			#print(w.serialize())
	except yaml.YAMLError as exc:
		print(exc)
