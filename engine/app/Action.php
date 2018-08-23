<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Actions\DoubleLinkedIF; 
use App\Actions\LinkedExecution;
use App\Actions\ActionFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Actions\WorkflowAction;

class Action extends Model 
                    implements DoubleLinkedIF, 
                               LinkedExecution{
    
    //use SoftDeletes;
    
    const ON_ENTRY=1;
    
    const ON_EXIT=2;
    
    public static $TYPE = [1 => 'ON_ENTRY', 2 =>'ON_EXIT' ];
    
    public function activity(){
        return $this->belongsTo("App\Activity");
    }
    
    public function next(){
        $this->nextAction()->first();
    }
    
    public static function create($type = "rest"){
        Log::info("actions.".$type);
        $source = Config::get("actions.".$type);
        Log::debug("Source: ".$source);
        $action = new $source;
        if($source instanceof WorkflowAction){
            return $action;
        }else{
            throw new Exception("La implementacion de Action no es de tipo WorkflowAction");
        }
        
    }
    
    public function execute(){
        $action_imp = Action::create($this->class);
        $action_imp->execute($this->config);
        Log::debug("Ejecutando actividad");
        Log::debug(json_decode($this->config));
    }
    
    
    public static function getType($id){
        return Action::$TYPE[$id];
    }
    
    public function nextAction(){
        return $this->hasOne('App\Action',"id_prev_action");
    }
    
    public function prevAction(){
        return $this->hasOne('App\Action',"id_next_action");
    
    }

    public function getNextId() {
        return $this->id_next_action;
    }

    public function getPrevId() {
        return $this->id_prev_action;
    }

    public function setNextId($id) {
        $this->id_next_action = $id;
    }

    public function setPrevId($id) {
        $this->id_prev_action = $id;
    }

    public function getNextNode() {
        return $this->nextAction()->first();
    }

    public function getPrevNode() {
        return $this->prevAction()->first();
    }
    
    public function saveMove(){
        $this->saveOrFail();
    }

    public function getNodeId() {
        return $this->id;
    }

}
