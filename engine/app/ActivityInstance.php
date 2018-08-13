<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\ActivityVariables;
use App\Events\ActivityEvents;

class ActivityInstance extends Model implements ActivityEvents{
    
    //private $state = ActivityEvents::IDLE;
    
    public function activity(){
        return $this->belongsTo("\App\Activity","activity_id");
    }
    
    public function stages(){
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
    
//    public function next(ProcessInstance $process){
//        try{
//            switch($this->activity_state){
//                case ActivityEvents::IDLE:
//                    $this->onEntry();
//                    break;
//                case ActivityEvents::ON_ENTRY;
//                    $this->onActivity();
//                    break;
//                case ActivityException::ON_EXIT:
//                    $this->onExit();
//                    break;
//                case ActivityInstance::FINISHED:
//                    return $this;
//                case ActivityEvents::PENDDING:
//                    return $this;
//                default:
//                    throw new ActivityException("El estado [".$this->activity_state."], no existe");
//            }
//            $this->next($process);
//        }catch(Exception $e){
//            $this->activity_state = ActivityEvents::ERROR;
//            $this->save();
//            log::error("[Event:OnExit][Id: $this->id]:",$e->getMessage());
//            return;
//        }        
//        
//    }
    
    private function execute(){
        //Se ejecutan las etapas en este punto
        $stage = $this->stages();
        if($stage != null ){
            //Ejecutar las etapas
            $this->stage = $stage->id;
            $this->activity_state = ActivityEvents::PENDDING;
        }else{
            Log::debug("No hay stages");
            $this->activity_state = ActivityEvents::ON_EXIT; //Para el siguiente evento
        }
        $this->save();
    }
    /**
     * 
     * @return type 
     */
    public function onActivity() {
        $this->activity_state = ActivityEvents::ON_ACTIVITY;
        $this->save();
        $process_instance = $this->process()->first();
        
        $activity = $this->activity()->first();
        
        Log::debug('Ejecutando actividad');
        if($this->type == "conditional"){
            //entonces hay varias condiciones
            $transitions = $activity->outputTransitions()->get();
            foreach($transitions as $transition){
                if( $transition->evaluate() ) {  //Si es correcta, el camino es por acá
                    $next_activity = Activity::find($transition->next_activity_id);
                    return $next_activity->newActivityInstance($process_instance);
                }
            }
        }else{
            $this->execute();
            Log::debug('Tipo actividada: Activity');
            Log::debug($activity->name);
            Log::debug("Estado; ". $this->activity_state);
            if($this->activity_state == ActivityInstance::ON_EXIT){  //trawer las transcciones
                
                $transition = $activity->outputTransitions()->first();
                
                if($transition!= null){ 
                    $transition->evaluate($this);
                    $next_activity = Activity::find($transition->next_activity_id);
                    return $next_activity->newActivityInstance($process_instance);
                }else{
                    Log::debug("Transicion nula");
                }
                return null;
            }
        }        
    }

    public function onEntry() {
        $this->activity_state = ActivityEvents::ON_ENTRY;
        $this->save();
        $root_action = $this->hasMany("App\Action","id_activity")
                ->where("id_prev_action")
                ->where("type",Action::ON_ENTRY)
                ->first();
        if($root_action != null){
            Actions\LinkedExecutionHandler::executeChain($root_action);
        }
    }

    public function onExit() {
        
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
