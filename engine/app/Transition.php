<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Hoa\Ruler\Context;
use Illuminate\Support\Facades\Log;

class Transition extends Model
{
    //
  
    public function evaluate(ActivityInstance $instance){
        //$instance = Activity::find($this->prev_activity_id);
        Log::debug('Ejecutando validacion de reglas');
        $vars = $instance->globalVariables();
        $ruler = new \Hoa\Ruler\Ruler();
        $rule = $this->condition;
        $context = new Context();
        
        //reemplazar valores en condicion
        foreach($vars as $var ){
            Log::debug("Var ". $var->name);
            $context[$var->name] = $var->value;
        }
        $result = $ruler->assert($rule, $context);
        var_dump($result);die;
        return $result;
 
    }
}
