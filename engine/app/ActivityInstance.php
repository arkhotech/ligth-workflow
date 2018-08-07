<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityInstance extends Model{
    
    public function activity(){
        return $this->belongsTo("\App\Activity");
    }
    
    public function variables(){
        return $this->many("App\ActivityVarible");
    }
    
    public function process(){
        return $this->belongTo("App\ProcessInstance"); 
    }
    
    public function nextStage(){
        
    }
    
    public function next(){
        $activity = $this->activity()->first();
        //$this->execAction($activity->getPreAction());
        //Si hay stages, entonces recueprar y ejecutar el primero
        if($this->type == "xx"){
            //entonces hay varias condicioes
        }else{
            $transition = $activity->outputTransitions()->first();
            
            if($transition!= null){ 
                $activity = Activity::find($transition->next_activity_id);
                return $activity::newActivityInstance();
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
