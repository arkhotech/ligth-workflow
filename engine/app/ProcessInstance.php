<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Events\ActivityEvents;
use App\Exceptions\ActivityException;
use App\Exceptions\NotUserInRoleException;
/**
 * Sec crea a partir de una definición Process
 */
class ProcessInstance extends Model implements ActivityEvents{

    private $state = ActivityEvents::IDLE;
    
    public static $STATES=[ 
                0 => "IDLE",
                1 => "ON_ENTRY",
                2 => "ON_ACTIVITY",
                3 => "ON_EXIT",
                4 => "PENDDING",
                5 => "FINISHED",
                6 => "ERROR"];
    
    public function __construct() {
        parent::__construct();
        $this->state = ActivityEvents::IDLE;
    }
    
    public function currentActivityInstance(){
        return $this->hasOne("App\ActivityInstance")->where("id",$this->activityCursor)->first();
    }
    
    public function activitiesInstances(){
        return $this->hasMany("App\ActivityInstance");
    }
    
    public function variables() {
        return $this->hasMany("App\ProcVarInstance", "id_process_instance");
    }

    public function exportVariables() {
        $variables = $this->variables()->select("name", "value")->get();
        return array("variables" => $variables);
    }
    /**
     * 
     * @param type $user Usuario que inicia el proceso
     * @return type La primera actividad del proceo
     * @throws \ActivityException
     */
    public function run($user){
        $activity = Activity::where("process_id", $this->process_id)
                ->where("start_activity", 1)
                ->first();
        if($activity==null){
            throw new \ActivityException("Error. No existe actividad de inicio");
        }
        if(!$activity->userCanStart($user)){
            throw new NotUserInRoleException("El usuario no puede iniciar la actividad");
        }
        $inst_activity = $activity->newActivityInstance($this,$user);
        return $inst_activity;
    }

    /**
     * @deprecated        
     */
    public function onActivity() {
         //iniciar prerequisitos;
        //Se debe buscar la primera actividad asociada para crear una instancia
        
        
        //iniciar postrequisitos;
    }

    public function onEntry() {
        //No implementada aún
    }

    public function onExit() {
        //no implementada aún
    }

}
