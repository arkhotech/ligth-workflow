<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\ActivityVariables;

class ActivityInstance extends Model{
    
    public function activity(){
        return $this->belongsTo("\App\Activity");
    }
    
    public function variables(){
        return $this->many("App\ActivityVarible");
    }
    
    public function process(){
        return $this->belongsTo("App\ProcessInstance"); 
    }
    
    public function declaredVariables(){
        return $this->hasMany("App\ActivityVariable","id_activity");
    }
    
    public function nextStage(){
        
    }
    
    public function next(){
        $activity = $this->activity()->first();
        //$this->execAction($activity->getPreAction());
        //Si hay stages, entonces recueprar y ejecutar el primero
        if($this->type == "conditional"){
            //entonces hay varias condiciones
            $transitions = $activity->outputTransitions()->get();
            foreach($transitions as $transition){
                if( $transition->evaluate() ) {  //Si es correcta, el camino es por acá
                    $next_activity = Activity::find($transition->next_activity_id);
                    return $next_activity->newActivityInstance();
                }
            }
        }else{
            Log::debug($activity->name);
            $transition = $activity->outputTransitions()->first();
            
            if($transition!= null){ 
                $transition->evaluate($this);
                $next_activity = Activity::find($transition->next_activity_id);
                
                return $next_activity->newActivityInstance();
            }else{
                Log::debug("Transicion nula");
            }
            return null;
        }
        //$this->execAction($activity->getPostAction());
    }
    
    private function execAction(Action $action ){
        if($action != null){
            $action->execute();
        }
    }
    
}
