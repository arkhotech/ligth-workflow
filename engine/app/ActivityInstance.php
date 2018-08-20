<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Events\ActivityEvents;

class ActivityInstance extends Model implements ActivityEvents{
    
     public function __construct() {
        parent::__construct();
        $this->activity_state = ActivityEvents::IDLE;
    }
    
    public function activity(){
        return $this->belongsTo("\App\Activity","activity_id");
    }
    
    public function stagesInstances(){
        return $this->hasMany("App\StageInstance","activity_instance_id");
    }
    
    public function actualStage(){
        return $this->hasOne("App\StageInstance","activity_instance_id");
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
        //Traere la definicion de stages. Y ejecutar el primer stage
        $activity = $this->activity()->first();
        $stage = $activity->stages()
                ->whereNull("prev_stage")
                ->first();

        if($stage != null ){
            //Ejecutar las etapas
            $this->stage = $stage->id;
            $stage_instance = $stage->newStageInstance($this);
            $stage_instance->onActivity();
            $this->activity_state = ActivityEvents::PENDDING;
        }else{
            Log::warning("No hay stages");
            $this->activity_state = ActivityEvents::ON_EXIT; //Para el siguiente evento
        }
        $this->save();
    }
    
    public function executeActivity(){
        $instance = $this->onEntry();
        if($instance == null){
            Log::info('LOPP finalizado');
        }else{
            Log::info("Retornando status : " . $this->activity_state);
        }
        return $instance;
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
//#####  Ejeución condicional (No probada aún)            
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
//#####  Ejeucion de stages
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
                    Log::debug("Retnornando actividad en modo pending");
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
//Obtener el primer form        
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
