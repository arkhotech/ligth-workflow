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
                Log::debug('Mapeando variable: '.$field->name."-".$this->id);
                
                $i_field = FieldInstance::where("name",$field->name)
                        ->where("form_instance_id",$this->id)->first();

                if($i_field == null){
                    Log::debug('Creando variable: '.$field->name);
                    $i_field = $field->createFieldInstance($this);
                }
                Log::debug("valor: ".$variables[$field->name]);
                $i_field->value = $variables[$field->name];
                $i_field->save();
                if( $i_field->validate() ){
                    Log::debug("Guardando valor en campo: ".$i_field->value);
                    ;
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
        //Aejecutar las acciones sobre cada variable
        $fields = $this->fields()->with('field')->get();
        $output = array();
        foreach($this->fields()->get() as $field){
            $field->evaluate();
            if($field->field->output_field){
                $output[] = ["name" => $field->name , "value" => $field->value];
            }
        }
        return $output;
    }
    
}
