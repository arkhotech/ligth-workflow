<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model{
    //
    const editable_fields = [ 'name'];
    
    public function activities(){
        return $this->hasMany("\App\ActivityInstance");
    }
    
    public function process(){
        return $this->belongsTo('\App\Process');
    }
    
    public function newActivityInstance(){
        $instance = new ActivityInstance();
        $instance->process_instance_id = $this->process_id;
        $instance->activity_id = $this->id;
        $instance->save();
        return $instance;
    }
}
