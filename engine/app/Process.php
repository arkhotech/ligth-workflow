<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ProcessInstance;

class Process extends Model{
    
    const fields = ["name","domain_id","role_owner_id"];

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
        $instance->state = "active";
        $instance->activityCursor = 0;
        $instance->save();
        return $instance;
    }
    
}
