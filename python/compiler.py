import sys
sys.path.append("./lib/python3.7/site-packages")

import yaml
from engine import Workflow, Activity, Transition
import json

#Para reemplazar los valores None por null, Al parecer no funciona
def represent_none(self, _):
    return self.represent_scalar('tag:yaml.org,2002:null', '')

yaml.add_representer(type(None), represent_none)

#abrir el YAML y pasarlo a JSON
with open("workflow.yml",'r') as stream:
	try:
		inp = json.dumps(yaml.safe_load(stream))
		spec = json.loads(inp)
		workflows = spec['workflows']

		for wf in workflows:
			w = Workflow(spec=wf)
			print('Ejecutando')
			#print(w.serialize())
			w.execute({"params": 1})

	except yaml.YAMLError as exc:
		print(exc)