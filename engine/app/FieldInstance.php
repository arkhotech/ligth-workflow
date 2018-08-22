<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FieldInstance extends Model
{
    protected $primaryKey = "id";
  
    public function activityInstance(){
        return $this->belongsTo("App\ActivityInstance");
    }
    
    public function field(){
        return $this->belongsTo("App\Field");
    }
    
    public function fieldInstance(){
        return $this->hasMany("App\FieldInstance");
    }
    
    public function validate(){
        return true;
    }
    
    public function validationError(){
        return "Not Implemented";
    }
    
    public function evaluate(){
        Log::debug("Evaluando campo: ".$this->name);
        $field_def = $this->field()->first();
        Log::debug($field_def->validation);
        //Agregar todo lo necesario pra validar una variable acá.
    }
}
