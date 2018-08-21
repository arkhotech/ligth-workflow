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
    
    public function execute(){
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
    
    public function onActivity() {
        $this->execute();
    }

    public function onEntry() {
       Log::debug("[Stage: OnEntry]");
        
    }

    public function onExit() {
        Log::debug("[Stage: OnExit]");
        return $this->form;
    }
    

     public function nextStage(){
//        $form_instance = $event->getSourceForm();
//                
//        $stage_instance = $form_instance
//                        ->stageInstance()
//                        ->first();
        $activity_instance = $this->activityInstance()->first();
        //Definiciond,para determinar el siguiente
        $stage = $this->stage()
                        ->first();
                //TODO So es nulo Termina ->  Y hay un evento para termno 
                //de actividad. Quizá no es bena idea revisar
                //esto en este evento...
                //Buscar la siguiente definicion de stage
        if( $stage->next_stage == null ){
            //TODO enviar evento de fin
            Log::debug("Fin de la actividad");
            event(new FinishActivityEvent($this));
            return null;
        }
        $next_stage = Stage::find($stage->next_stage);
        //Crear la nueva instancia
        $new_stage_instance = $next_stage->newStageInstance($activity_instance);
        //Asginar el nuevo stage a la actividad actual.
        $activity_instance->stage = $new_stage_instance->id;
        $activity_instance->save();
        $new_stage_instance->createFormInstance();
        return $new_stage_instance;
    }
}
