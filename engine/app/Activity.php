<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ActivityException;
use App\User;

class Activity extends Model implements EditableFieldsIF{
    
    public static $STATES = ['active','finished','error'];
    
    const ACTIVITY = 'activity';
    
    const FORK = 'fork';
    
    const JOIN = 'join';
    
    public function stages(){
        return $this->hasMany("App\Stage");
    }
    
    public function forms(){
        return $this->hasMany("App\Forms");
    }
    
    public function roles(){
        return $this->belongsToMany("App\Role","activity_roles");
    }
    
    public function actions(){
        return $this->hasMany('App\Action');
    }
    
    public function getPreAction(){
        return Action::find($this->pre_activity);
    }
    
    public function getPostAction(){
        return Action::find($this->port_activity);
    }
    /**
     * @deprecated
     * @return type
     */
    public function activities(){
        return $this->hasMany("\App\ActivityInstance");
    }
    
     public function instances(){
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
    
    public function newActivityInstance(ProcessInstance $proc_inst,$path_token = null){
        $user = User::find(1);  //usuario admnistrador
        
        $instance = new ActivityInstance();
        $instance->process_instance_id = $proc_inst->id;
        $instance->activity_id = $this->id;
        $instance->activity_state = 0;
        $instance->assigned_user = ( $user === null ) ? 1 : $user->id;
        $instance->flow_path_id = $path_token;
        $instance->type = $this->type;
        $instance->save();
        return $instance;
    }

    public function fields() {
        return [ 'name','start_activity','end_activity','type','pre_activity',"post_activity"];
    }
    
    /**
     * Chekea si el 
     * @param type $user
     * @return boolean
     */
    public function userCanStart(User $user){
        
        $user_roles = $user->roles()->select('id')->get();
        $exists = ActivityRole::where("activity_id",$this->id)
                ->whereIn("role_id",$user_roles)->first();
        
        return ( $exists == null ) ? false : true;
    }

}
