<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Actions\DoubleLinkedIF; 
use App\Actions\LinkedExecution;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Action extends Model implements DoubleLinkedIF, LinkedExecution{
    
    //use SoftDeletes;
    
    const ON_ENTRY=1;
    
    const ON_EXIT=2;
    
    public static $TYPE = ['ON_ENTRY' => 1,'ON_EXIT' => 2];
    
    public function next(){
        $this->nextAction()->first();
    }
    
    public function execute(){
        //$action = ActionFactory::create(self::$TYPE[$this->type]);
        //$action->execute();
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
