<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Actions\DoubleLinkedIF;


class Field extends Model implements DoubleLinkedIF
{
    //
    public function fieldInstances(){
        return $this->hasMany("App\FieldInstance");
    }
    
    public function createFieldInstance(FormInstance $form){
        $field = new FieldInstance();
        $field->name = $this->name;
        $field->form_instance_id = $form->id;
        $field->field_id = $this->id;
        $field->save();
        return $field;
    }
    
    public function getNextId() {
        return $this->next_field;
    }

    public function getNextNode() {
        if($this->next_field!=null){
            return Field::find($this->next_field);
        }
    }

    public function getNodeId() {
        return $this->id;
    }

    public function getPrevId() {
        return $this->prev_field;
    }

    public function getPrevNode() {
         if($this->prev_field!=null){
            return Field::find($this->prev_field);
        }
    }

    public function saveMove() {
        $this->save();
    }

    public function setNextId($id) {
        $this->next_field = $id;
    }

    public function setPrevId($id) {
        $this->prev_field = $id;
    }

}
