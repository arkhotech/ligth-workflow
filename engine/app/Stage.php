<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Actions\DoubleLinkedIF;
use App\EditableFieldsIF;

class Stage extends Model implements DoubleLinkedIF,  EditableFieldsIF{
    
    public static $TYPE = array("FORM" => 1, "CHOOSE" => 2);
    
    const FORM = 1;
    
    const CHOOSE = 2;
    
    public function newStageInstance(ActivityInstance $activity_instance){
        $instance  = new StageInstance();
        $instance->type = $this->type;
        $instance->activity_instance_id = $activity_instance->id;
        $instance->save();
        return $instance;
    }
    //
    public function activity(){
        return $this->belongsTo("App\Activity");
    }
    
    public function forms(){
        return $this->hasMany('App\Form');
    }

    public function getNextId() {
        return $this->next_stage;
    }

    public function getNextNode() {
        return $this->hasOne("App\Stage","prev_stage");
    }

    public function getNodeId() {
        return $this->id;
    }

    public function getPrevId() {
        return $this->prev_stage;
    }

    public function getPrevNode() {
        return $this->hasOne("App\Stage","next_stage");
    }

    public function saveMove() {
        $this->save();
    }

    public function setNextId($id) {
        $this->next_stage = $id;
    }

    public function setPrevId($id) {
        $this->prev_stage = $id;
    }

    public function fields() {
        return ["type","name","descripcion"];
    }

}
