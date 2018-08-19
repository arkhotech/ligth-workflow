<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    //
    public function fields(){
        return $this->hasMany('App\Field');
    }
    
    public function createFormInstance(StageInstance $stg_inst){
        $form = new FormInstance();
        $form->stage_instance_id = $stg_inst->id;
        $form->save();
        
        $fields = $this->fields()->get();
        foreach($fields as $field){
            $field->createFieldInstance($form);
        }
        return $form;
    }
    
}