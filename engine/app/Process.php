<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\ProcessInstance;
use App\EditableFieldsIF;
use App\Events\Events;

class Process extends Model implements EditableFieldsIF{

    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
    
    public function domain(){
        return $this->belongsTo("App\Domain");
    }
    
    public function processInstances(){
        return $this->hasMany("App\ProcessInstance");
    }
    
    public function activities(){
        return $this->hasMany("App\Activity");
    }
    
    public function declaredVariables(){
        return $this->hasMany('App\Variable');
    }
    
    public function roles(){
        return $this->belongsToMany("App\Role","process_roles");
    }
    
    public function newProcessInstance(User $user){
        if($user !== null  && $this->userCantStart($user) ){ 
            Log::debug("Creando instancia");
            $instance = new ProcessInstance();
            $instance->setUser($user);
            $instance->process_id = $this->id;
            $instance->process_state = Events::IDLE;
            $instance->activityCursor = 0;
            $instance->asynch = $this->asynch;
            $instance->save();
            return $instance;
        }else{
            throw new NotUserInRoleException(
                    "[ProcessInstance]: El usuario [$user->name] no puede iniciar la actividad");
        }
    }
    
    private function userCantStart(User $user){
        $user_roles = $user->roles()->select('id')->get();
        $exists = ProcessRole::where("process_id",$this->id)
                ->whereIn("role_id",$user_roles)->first();
        
        return ( $exists == null ) ? false : true;
    }
    

    public function fields() {
        return ["name","domain_id","role_owner_id"];
    }

}
