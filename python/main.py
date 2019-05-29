from engine import Workflow, Activity, Transition


# be = Activity('Marcelo')
# wf = Workflow(id='5ce5adeb3a90d90a6b18bf4c')
# print(wf.id)
# print(wf.name)

myquery = { "nombre": "Marcelo" }

retrieve = False

if retrieve:
	wf1 = Workflow(id='5cec6ac45b6dd8069cdb05a8')
	print('#######################')

	print(wf1.activities.keys())
	for a in wf1.activities.keys():
		print(a)
		if 'inicio' == a:
			activity = wf1.activities[a]
			activity.condition = 'params + 10'
		act = wf1.activities[a]
		print(act.type)
	


	print('Ejecutando')
	wf1.execute({"params" : 5 })
	#act = Activity(name = 'fin' , _type='end',workflow = wf1 )
	#print(wf1.serialize())
	#print(wf1.save())
else:

	wf2 = Workflow(name='Test',desc='Es una prueba')
	a1 = Activity(name='inicio',_type='init',workflow=wf2)
	b1 = Activity(name='B1',_type='end',workflow=wf2)
	b2 = Activity(name='B2',_type='end',workflow=wf2)

	t1 = Transition(name='to_a',next=b1,cond='params == 1')
	t2 = Transition(name='to_b',next=b2,cond='params == 2')

	a1.addTransition(t1)
	a1.addTransition(t2)

	wf2.addActivity(a1)
	


	print(wf2.serialize())
	#print(wf2.save())


# a1 = Activity(name='fin',_type='end',workflow=wf2)

# 

# mydoc = coll_engine.find(myquery)

# for x in mydoc:
#   print(x) 

  

'''
asdas
'''

#test = {  "nombre": "Marcelo", "apellido": "Silva"}

#record = coll_engine.insert_one(test)

#print(record.inserted_id)


#print(mydb.list_collection_names())

"""

db.workflows.update(  { "_id" : "5ce832d2f1aef38ca021a716" } , { transitions: 
   { a: { prev: '', name: 'a', next: 'inicio' },
     b: { prev: '', name: 'b', next: 'inicio' } } } )


 {"activities" : { "inicio" : { "type" : "init", "name" : "inicio", "transitios" : { "a" : { "prev" : "", "name" : "a", "next" : "inicio" }, "b" : { "prev" : "", "name" : "b", "next" : "inicio" } } } }, "description" : "Es una prueba", "name" : "Test", "id" : "" }



db.wrokflows.insert(
 { activities: { inicio: { type: 'init', name: 'inicio', transitions:  { a: { prev: '', name: 'a', next: 'inicio' },
     b: { prev: '', name: 'b', next: 'inicio' } } } },
  description: 'Es una prueba',
  name: 'Test',
  id: '' })


  db.workflows.remove({ "_id" : "5ce832d2f1aef38ca021a716" })

"""

