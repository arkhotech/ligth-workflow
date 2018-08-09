<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transition extends Model
{
    //
    
    
    public function evaluate(){
        $instance = ActivtyInstance::find($this->prev_activity_id);
        $vars = $instance->declaredVariables()->get();
        //reemplazar valores en condicion
        foreach($vars as $var ){
            $this->condiction;
        }
        return true;
    }
}
