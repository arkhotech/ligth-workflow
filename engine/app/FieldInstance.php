<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FieldInstance extends Model
{
    
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
    
}
