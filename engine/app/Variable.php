<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\EditableFieldsIF;
use App\ProcVarInstance;
use Illuminate\Support\Facades\Log;

class Variable   
        extends Model 
            implements EditableFieldsIF {
    //
    //private $fields = ["name","value","type"]; 
    
    const TYPES = ["string" ,"integer", "array", "json", "rut" ,"email", "phone"];
    
    public function fields() {
        return ["name","value","type"];
    }
    
    public function createInstance(ProcessInstance $proc_inst){
        $var = new VariableInstance();
        foreach($this->fields() as $field){
            $var->{$field} = $this->{$field};
        }
        $var->variable_id = $this->id;
        $var->process_instance_id = $proc_inst->id;
        $var->save();
        return $var;
    }
    
    public function save(array $options = array()) {
        parent::save($options);
        /*if($this->type != null && key_exists($this->type,self::TYPES)){
            parent::save($options);
        }else{
            Log::error("Tipo de campo no soportado: ".$this->value);
            //throw new Exception("Tipo de campo no soportado: ".$this->value);
        }*/
    }
    
}
