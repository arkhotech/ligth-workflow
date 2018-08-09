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
        $vars = $instance->declaredVariables()
                ->select('id','name','value')
                ->get();
        var_dump($vars);die;
        
        $rule = $this->condition;
        $context = new Context();
        
        //reemplazar valores en condicion
        foreach($vars as $var ){
            Log::debug("Var ". $var->name);
            $context[$var->name] = $var->value;
        }
        return $ruler->assert($rule, $context);
  
    }
}
