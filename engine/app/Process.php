<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ProcessInstance;
use App\EditableFieldsIF;
use App\Events\ActivityEvents;

class Process extends Model implements EditableFieldsIF{

    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
    
    public function domain(){
        return $this->belongsTo("App\Domain");
    }
    
    public function instances(){
        return $this->hasMany("App\ProcessInstance");
    }
    
    public function activities(){
        return $this->hasMany("App\Activity");
    }
    
    public function declaredVariables(){
        return $this->hasMany('App\ProcessVariable','id_process');
    }
    
    public function createInstance(){
        $instance = new ProcessInstance();
        $instance->process_id = $this->id;
        $instance->process_state = ActivityEvents::IDLE;
        $instance->activityCursor = 0;
        $instance->asynch = $this->asynch;
        $instance->save();
        return $instance;
    }

    public function fields() {
        return ["name","domain_id","role_owner_id"];
    }

}
