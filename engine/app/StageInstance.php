<?php

namespace App;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StageInstance
 *
 * @author msilva
 */
use Illuminate\Database\Eloquent\Model;
use App\Events\ActivityEvents;
use App\EditableFieldsIF;
use App\Actions\DoubleLinkedIF;
use Illuminate\Support\Facades\Log;
use App\Form;


class StageInstance extends Model implements   EditableFieldsIF, ActivityEvents{
    
    private $form;
    
    public function formInstances(){
        return $this->hasMany("App\FormInstance");
    }
    
    public function activityInstance(){
        return $this->belongsTo("App\ActivityInstance");
    }
    
    public function stage(){
        return $this->belongsTo("App\Stage");
    }
    
    public function fields() {
        
    }
    
     public function createFormInstance(){
        //Buscar el formulario de inicio del set perteneciente a este stage
        $activity_instance = $this->stage()->first();
        $defForm =$activity_instance->forms()->whereNull("prev_form")->first();
        
        return $defForm->createFormInstance($this);
    }
    
    
    public function onActivity() {
        Log::debug("[Stage: OnActivity]:".$this->id);
        //Cargar la definicion de stage
        $stage = $this->stage()->first();
        if($stage!=null){
            $form_instance = $this->createFormInstance();
        }else{
            Log::warning('No existe instancia asociada a esta instancia');
            throw new Exceptions\ActivityException('No existe instancia asociada a esta instancia');
        }
    }

    public function onEntry() {
       Log::debug("[Stage: OnEntry]");
        
    }

    public function onExit() {
        Log::debug("[Stage: OnExit]");
        return $this->form;
    }
    
    public function execute(){
        $this->onActivity();
        $this->onActivity();
        return $this->onExit();
    }

}
