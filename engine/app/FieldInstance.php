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
    //
    public function newFieldInstance(ActivityInstance $activity_instance){
        $instance = new FieldInstance();
        $instance->name = $this->name;
        $instance->value = $this->value;
        $instance->field_id = $this->id;
        $instance->activity_instance_id = $activity_instance->id;
        $instance->save();
    }
}
