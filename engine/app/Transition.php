<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Hoa\Ruler\Context;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;

class Transition extends Model
{
    //
  
    public function evaluate(ActivityInstance $instance){
        //$instance = Activity::find($this->prev_activity_id);
        Log::debug('[Transition][Evaluate] Ejecutando validacion de reglas');
        if($this->condition === null || $this->condition == "" || $this->condition === 0){
            Log::debug("[Transition][$this->name] No hay condición para evaluar");
            return true;
        }
        $vars = $instance->globalVariables();
        $ruler = new \Hoa\Ruler\Ruler();
        $rule = $this->condition;
        $context = new Context();
        
        //reemplazar valores en condicion
        foreach($vars as $var ){
            Log::debug("[Transition][Evaluate][Var] Injectando". $var->name);
            $context[$var->name] = $var->value;
        }
        $result = $ruler->assert($rule, $context);
        Log::debug("[Transition][Evaluate][Result] ".(($result) ? "true" : "false"));
        return $result;
 
    }
    
    public static function createPathId(){
        return Uuid::generate();
    }
}
