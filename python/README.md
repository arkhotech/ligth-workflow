## Workflow handler (Python)


Version de python: 3.7.3

###  Librerías:

* boto3



# Sytaxis

Workflow especificaction:



```YAML
version: "0"

workflow:
   -  DEFINITIONS

```

DEFINIONS

```YAML
name:  Sting
description: String
activities:
   STRING:
      ACTIVITY_DEFINITION 
```

ACTIVITY_DEFINITION

```YAML

type:  "init" | "end"  (optional)
execution_type:  sync | async (default sync)
actions:
   - ACTION_DEFINTION
   - .... 
transitions:
	- TRANSITION
	- ... 
callback:  #required if execution_type is async
	actions:
		ACTION_DEFINTION
		
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

This definition execute some especfic and concrete work inside the workflow. The action have the following required properties:

* input:  Especified one or more input parameters. The parameter is procesed like a JSON key-values list whatever if the input is a struct with several leveles, always the fisrt properties will be proceced like keys and the second level like a value.
* operation: Especify the concrete operation that will execute this action. This property depends of the action type.
* output: Especify the name of tha output parameter

```JSON
{
   "price": 50590
   "product": "bluetooth mouse"
   "id": "xxxxxxxxxyyyyyyy"
}
```
### Examples:

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
##SELECT

The select sentence do a selection from input and assign the selected variables to a new variables.  The "select" sentence must be insede of the input or output

```YAML

- type: "Evaluate"
  input:
  	select:
  	  a: "price"
  	  b: "product"
  	  
  	  
input will be:
  {
     a: 50590
     b: "
  }

```
