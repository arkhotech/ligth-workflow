<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Events\ActivityEvents;

class ActivityInstance extends Model implements ActivityEvents{
    
    //private $state = ActivityEvents::IDLE;
    
     public function __construct() {
        parent::__construct();
        $this->state = ActivityEvents::IDLE;
    }
    
    public function activity(){
        return $this->belongsTo("\App\Activity","activity_id");
    }
    
    public function stages(){
        $activity = $this->activity()->first();
        if( $activity != null ){
            return $activity->stages();
        }
        return null;
    }
    /**
     *
     * @return type
     */
    public function variables(){
        return $this->hasMany("App\ActivityVariable");
    }
    
    public function globalVariables(){
        $process_instance = $this->process()->first();
        return $process_instance->variables()->get();
    }
    
    public function process(){
        return $this->belongsTo("App\ProcessInstance","process_instance_id"); 
    }
    /**
     * Trae las variables declaradas en el proecso
     * @return type
     */
    public function declaredVariables(){
        return $this->hasMany("App\ActivityVariable","id_activity");
    }

    private function executeStages(){
        //Se ejecutan las etapas en este punto
        $stage = $this->stages()->first();
        if($stage != null ){
            //Ejecutar las etapas
            $this->stage = $stage->id;
            $instance = $stage->newStageInstance();
            $instance->onActivity();
            $this->activity_state = ActivityEvents::PENDDING;
        }else{
            Log::debug("No hay stages");
            $this->activity_state = ActivityEvents::ON_EXIT; //Para el siguiente evento
        }
        $this->save();
    }
    
    public function executeActivity(){
        return $this->onEntry();
    }
    /**
     * 
     * @return type 
     */
    public function onActivity() {
        Log::debug("[onActibity]");
        //Cambia el estado a onActivity
        $this->activity_state = ActivityEvents::ON_ACTIVITY;
        $this->save();
        //trae la definicion del proceso
        $process_instance = $this->process()->first();
        //trae la definicion de la actividad
        $activity = $this->activity()->first();
        
        Log::debug('Ejecutando actividad');
        if($this->type == "conditional"){
            Log::debug("Ejecutando actividad condicional");
            //entonces hay varias condiciones
            $transitions = $activity->outputTransitions()->get();
            foreach($transitions as $transition){
                if( $transition->evaluate() ) {  //Si es correcta, el camino es por acá
                    $next_activity = Activity::find($transition->next_activity_id);
                    return $next_activity->newActivityInstance($process_instance);
                }
            }
        }else{
            Log::debug("Ejecutando stages");
            $this->executeStages();
            Log::debug('Tipo actividada: Activity');
            Log::debug($activity->name);
            Log::debug("Estado; ". $this->activity_state);
            
            switch($this->activity_state){
                case ActivityInstance::ON_EXIT:
                    $transition = $activity->outputTransitions()->first();
                    if($transition!= null){ 
                        $transition->evaluate($this);
                        $next_activity = Activity::find($transition->next_activity_id);
                        return $next_activity->newActivityInstance($process_instance);
                    }else{
                        Log::debug("Transicion nula. Se asume final del proceso");
                        $this->onExit();
                        return null;
                    }
                    
                    break;
                case ActivityInstance::PENDDING:
                    return $this;
                default:
                    throw new ActivityException("Estado desconocido ".$this->activity_state);
                    
            }

        }        
    }

    public function onEntry() {
        Log::debug("[onEntry]");
        $this->activity_state = ActivityEvents::ON_ENTRY;
        $this->save();
        $root_action = $this->hasMany("App\Action","id_activity")
                ->where("id_prev_action")
                ->where("type",Action::ON_ENTRY)
                ->first();
        if($root_action != null){
            Actions\LinkedExecutionHandler::executeChain($root_action);
        }
        return $this->onActivity();
    }

    public function onExit() {
        Log::debug("[onExit]");
        $root_action = $this->hasMany("App\Action","id_activity")
                    ->where("id_prev_action")
                    ->where("type",Action::ON_EXIT)
                    ->first();
        
        if($root_action != null){
            Actions\LinkedExecutionHandler::executeChain($root_action);
        }

        $this->activity_state = ActivityEvents::FINISHED;
        $this->save();
    }

}
