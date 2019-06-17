## Workflow handler (Python)


Version de python: 3.7.3

Data Base:  Mongo 

###  Librerías:

* boto3
* pymongo
* urllibs3


### Sytaxis

Workflow especificaction:



```YAML
version: "0"

workflow:
   -  DEFINITIONS

```

#### DEFINTIONS

```YAML
name:  Sting
description: String
activities:
   STRING:
      ACTIVITY_DEFINITION 
```

#### ACTIVITY_DEFINITION

```YAML

type:  "init" | "end"  (optional)
execution_type:  sync | async (default sync)
actions:
   - ACTION_DEFINTION
   - .... 
TRANSITIONS
CALLBACK  #required if execution_type is async

```
#### TRANSITIONS

```YAML

transitions:
	- TRANSITION
```

####TRANSITION

The transitions are the logical output of the flow. Are necesary by follow a logical path beetwen "activities".  An activity can have zero or serveral transitions. When an activity doesn't have any transition (zero) the workflow engine assume that it's a final activity and finalize the execution.

The transition can have a logical condition express on python sintax. This operation must to return a boolean value, then when an activity finalize execute an evaluation of all transitions and when a condition get a true result, the workflow select em like a output for the next activity.  

If there is a several transitions you must select one of em like a default transition, then the engine could select the default output if any condition return true.  

Whe there is only one transition you could leave without condition, in these case the transition it's taken like a default ouput

The next property, especify the next activity to execute.

```YAML

name:  String   (required)
next:   String
default: Boolean  (default false)
condition: Python  

```

**ACTION_DEFINTION**

```YAML
type:  Evaluate | CallLambda | Webhook
input: String
	SELECT
operation: String
output: String
	SELECT 
```

* **type**: Especify the type of activity. It's cloud be EValuate, CallLambda or Webhook.  
	* **Evaluate**: Execute an operation or python instruction. If you want to execute a Mathematical or logical operation, you must use python syntax.
	* **CallLambda**: Perform a lambda function call. The call will be do on Request Response mode.
	* **Webhook** (Not Implemented yet)


This definition execute some especfic and concrete work inside the workflow. The action have the following required properties:

* **input**:  Define the name of one or more input parameters. The parameter is procesed like a JSON key-values list whatever if the input is a struct with several leveles, always the fisrt level properties, will be proceced like keys and the internal levels like a value.
* **operation**: Especify the concrete operation that will execute this action. This property depends of the action type.
* **output**: Define the name of the output parameter. This parameter store the result of the operation.

The output variables will be pass to the next action on the actions lists like a input. All the output parameters will going  accumulated and stored during the activity execution.

```JSON
{
   "price": 50590
   "product": "bluetooth mouse"
   "id": "xxxxxxxxxyyyyyyy"
}
```
#### Examples:

```YAML

actions:
# Takes from input json the "price" property and apply the
# especified operation. The result of the operation wil be 
# save on the "taxes" variable, definied on output parameter.

  - type: "Evaluate"
    input: "price"
    operation: "price * 0.20"
    output: "taxes"
    
  - type: "CallLambda"
    input: "id"
    operation: "getDiscount"   #Call to "getDiscount" lambda function
    output: "disccount"
  
  - type: "Evaluate"
    input: "discount"  #get parameter from previous ouput
    operation: "price - (price * disccount)"
    output: "final_price"
   	           

```
##SELECT (version 0)

The select sentence do a selection from input and assign the selected variables to a new variable name.  The "select" sentence must be inside of the input or output

NOTE: the input values ​​are passed like parameters when you execute by first time the workflow and later will be passed the variable variables selected from the previus actions and so on.

```YAML

- type: "Evaluate"
  input:
  	select:
  	  a: "price"     #Assing "price" variable from input and assig these value to "a" variable.
  	  b: "product"   

#Finally the complete output of select could be the following JSON
  	  
input will be:
  {
     a: 50590
     b: "
  }

```

### CALLBACK

The callback element must be especify when an action it's defined like asynchronous. In these case, the action takes two steps for it's execution. The first one part it's excuted under "actions" sections then the executions it's sttoped and the workflow takes the "WAITTING" state.  
When the callback it's returned to the workflow, the engine execute the callback section.


```YAML

callback:
	actions:
		ACTION_DEFINTION
		
```

WORKFLOW EXAMPLE

```YAML
version: "0"

workflows:
  - name: "async-test"
    description: "prueba de acciones en actividad"
    activities:
      A1: 
        type: "init"
        execution_type: asynch   #async|sync
        actions:
          - type: "Evaluate"
            input:   #Refernecia a los parametros de entreda
              select:   #Seleccionar desde la entrada, los valores de a contunuacion
                porc: "porcentaje" #sooo tomar este parametro desde la entrada y asignarselo a params
                valor: "monto"
            operation: (porc / 100) * valor
            output:  "result"  #Nombre de la salida
        callback:
          actions:
            - type: "Evaluate"
              input: 
                select:
                  monto: "result"
                  impuestos: "iva"
              operation: "monto * impuestos"
              output: "total"
        transitions:
          - name: t1
            next: B1
            default: true
          - name: t2
            next: B1
            default: false
      B1:
        execution_type: synch
        actions: 
          - type: "Evaluate"
            input: 
              select:
                total: "total"
            operation:  "total / 10000"
            output: "resultado"
        transitions:
          - name: g1
            next: G2
            default: true
      G2:
        execution_type: asynch
        actions: 
          - type: "Evaluate"
            input: 
              select:
                ponderacion: "resultado"
            operation:  "ponderacion * 0.25"
            output: "reultado"
        callback:
          actions:
            - type: "Webhook"
              input: 
                select:
                  payload: "payload"

``