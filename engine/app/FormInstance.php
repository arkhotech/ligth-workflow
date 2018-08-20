<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Form;
use Exception;

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
    
    public function inputVariables(array $variables){
        
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
                if( $i_field->save() ){
                    Log::debug('Campo guardado correctamente');
                }
            }else{
                $error[] = ["campo.obligatorio" => $field->name];
                Log::error("No existe variable: ".$field);
            }
        }
        if(count($error)!=0){
            throw new Exception(json_encode($error));
        }
    }
    
    public function validate(){
        return true;
    }
    
    public function nextForm(){
        $form_def = $this->form()->first();
        
        $next_form_def = Form::find($form_def->next_form);
        $newform = $next_form_def->newFormInstance($this->stageInstance()->first());
        return $newform;
    }
    
}
