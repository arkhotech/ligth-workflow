<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model implements EditableFieldsIF{
    //
    //const editable_fields = [ 'name','start_activity','end_activity','type'];
    
    const STATES = ['active','finished','error'];
    
    public function getPreAction(){
        return Action::find($this->pre_activity);
    }
    
    public function getPostAction(){
        return Action::find($this->port_activity);
    }
    
    public function activities(){
        return $this->hasMany("\App\ActivityInstance");
    }
    
    public function process(){
        return $this->belongsTo('\App\Process');
    }
    
    public function outputTransitions(){
        //La transicion anterior aputa a esta clase
        return $this->hasMany('\App\Transition','prev_activity_id');
    }
    
    public function inputTransitions(){
        return $this->hasMany('\App\Transition','next_activity_id');
    }
    
    public function newActivityInstance(ProcessInstance $proc_inst){
        $instance = new ActivityInstance();
        $instance->process_instance_id = $proc_inst->id;
        $instance->activity_id = $this->id;
        $instance->state = 0;
        $instance->save();
        return $instance;
    }

    public function fields() {
        return [ 'name','start_activity','end_activity','type','pre_activity',"post_activity"];
    }

}
