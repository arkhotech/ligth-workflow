<?php

namespace App;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Illuminate\Database\Eloquent\Model;
use App\EditableFieldsIF;
use Illuminate\Support\Facades\Validator;
use App\Rules\BasicTypes;

class ProcVarInstance extends Model 
            implements EditableFieldsIF {
    
    protected $table = "process_vars_instances";
    
    public function processVariable(){
        return $this->belongsTo('App\ProcessVariable',"id_process_var");
    }
    
    public function fields() {
        return ["name","value"];
    }

    public function validate(){    
        return Validator::make(
                [ $this->value ], 
                [ new BasicTypes($this->type)] )
                ->passes(); // true
    }
}
