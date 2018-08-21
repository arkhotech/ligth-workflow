<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Form;
use Exception;
use App\Events\FormEvent;

class FormInstance extends Model
{
    //
    public function form(){
        return $this->belongsTo("App\Form");
    }
    
    public function stageInstance(){
        return $this->belongsTo("App\StageInstance");
    }
    
    public function fields(){
        return $this->hasMany("App\FieldInstance","form_instance_id");
    }
    /**
     * Se debe ejecutar la validación de las variables acá
     * @param array $variables
     * @return type
     * @throws Exception
     */
    public function injectInputVariables(array $variables){
        
        if($variables == null){
            Log::info("Sin variables de entrada");
            return;
        }
        $form = $this->form()->first();
        $declared_fields = $form->fields()->get();  
        $error = array();
        foreach($declared_fields as $field){
            if($field->required
                && key_exists($field->name, $variables) 
                && $variables[$field->name] != null ){
                Log::debug('Mapeando variable: '.$field->name);
                $i_field = $field->fieldInstances()->first();
                if($i_field == null){
                    $i_field = $field->createFieldInstance($this);
                }
                Log::debug("valor: ".$variables[$field->name]);
                $i_field->value = $variables[$field->name];
                
                if( $i_field->validate() ){
                    $i_field->save();
                }else{
                    $error[] = [ $i_field->name.".validation.error" 
                        => $i_field->validationError()];
                }
            }else{
                $error[] = ["campo.obligatorio" => $field->name];
                Log::error("No existe variable: ".$field);
            }
        }
        return $error;
    }
    
    public function execute(){
        Log::info("Ejecutando formulario");
        return $this->fields()->select(["name","value"])->get();
        //return $this->validate();
    }
    
    public function validate(){
        return true;
    }
    
//    public function nextForm(){
//        $form_def = $this->form()->first();       
//        if( $form_def->next_form == null ){
//            //No hay siguiente form. Entonces termina
//            Log::info('Form chain finished');
//            //event(new FormEvent($this,FormEvent::FINISH));
//            $current_stage = $this->stageInstance();
//            $next_stage = $current_stage->nextStage();
//            return $next_stage->formInstances()->first();
//        }
//        $next_form_def = Form::find($form_def->next_form);
//        $newform = $next_form_def->createFormInstance($this->stageInstance()->first());
//        return $newform;
//    }
    
   
    
}
