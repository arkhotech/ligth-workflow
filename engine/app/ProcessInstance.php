<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Events\Executable;
use App\Events\Events;
use App\Events\ProcessEvent;
use App\Events\ActivityEvent;
use App\Exceptions\ActivityException;
use App\Exceptions\NotUserInRoleException;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Sec crea a partir de una definición Process
 */
class ProcessInstance extends Model implements Executable{

    private $state = Events::IDLE;
    
    private $user;
    
    public function __construct() {
        parent::__construct();
        
        $this->state = Events::IDLE;
    }
    
    public function setUser($user){
        $this->user = $user;
    }
    
    public function currentActivityInstance(){
        return $this->hasOne("App\ActivityInstance")
                ->where("id",$this->activityCursor)
                ->first();
    }
    
    public function activitiesInstances(){
        return $this->hasMany("App\ActivityInstance");
    }
    
    public function variables() {
        return $this->hasMany("App\VariableInstance");
    }

    public function exportVariables() {
        $variables = $this->variables()->select("name", "value")->get();
        return array("variables" => $variables);
    }

    public function start() {
        Log::info("[ProcessInstance][Start] Ejecutando proceso");
         //iniciar prerequisitos;
        //Se debe buscar la primera actividad asociada para crear una instancia
         $activity = Activity::where("process_id", $this->process_id)
                ->where("start_activity", 1)
                ->first();
        if($activity==null){
            throw new ActivityException("Error. No existe actividad de inicio");
        }

        $inst_activity = $activity->newActivityInstance($this);
        //Nueva actividad
        event(new ActivityEvent($inst_activity,Events::NEW_INSTANCE));
        
        return $inst_activity;
    }

    public function init() {
        Log::info("[ProcessInstance][Start] Inicializando");
        //No implementada aún
        //Finalizado el evento
        event(new ProcessEvent($this,Events::ON_ACTIVITY));
    }

    public function end() {
        Log::info("[ProcessInstance][Fin] #####  FINALIZANDO PROCESO  #######");
        
    }

    public function handleError(Exception $e) {
        Log::error("[Error Handle] ".$this->getMessage());
    }

}
