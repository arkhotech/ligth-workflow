<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Events\ActivityEvents;
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
    
    public function variables() {
        return $this->hasMany("App\ProcVarInstance", "id_process_instance");
    }

    public function exportVariables() {
        $variables = $this->variables()->select("name", "value")->get();
        return array("variables" => $variables);
    }

    public function onActivity() {
         //iniciar prerequisitos;
        //Se debe buscar la primera actividad asociada para crear una instancia
        
        $act_instance = Activity::where("process_id", $this->process_id)
                ->where("start_activity", 1)
                ->first();
        if($act_instance==null){
            throw new \ActivityException("Error. No existe actividad de inicio");
        }
        $activity = $act_instance->newActivityInstance($this);
        return $activity;
        //iniciar postrequisitos;
    }

    public function onEntry() {
        //No implementada aún
    }

    public function onExit() {
        //no implementada aún
    }

}
