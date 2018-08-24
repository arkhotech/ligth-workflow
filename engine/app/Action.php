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
                    implements DoubleLinkedIF{
    
    use SoftDeletes;
    
    const ACTION_FINISHED_OK = 1;
    
    const ACTION_ERROR = 2;
    
    const ON_ENTRY=1;
    
    const ON_EXIT=2;
    
    public static $TYPE = [1 => 'ON_ENTRY', 2 =>'ON_EXIT' ];
    
    public function activity(){
        return $this->belongsTo("App\Activity");
    }
    
    public function createActionInstance($activity_instance){
        $instance = new ActionInstance();
        $instance->activity_instance_id = $activity_instance->id;
        $instance->class = $this->class;
        $instance->name = $this->name;
        $instance->action_id = $this->id;
        $instance->config = $this->config;
        $instance->save();
        return $instance;
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
