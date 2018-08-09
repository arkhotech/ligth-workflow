<?php
namespace app;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Esta interfaces es usada para la determinar que campos son editables al ser
 * usadas en una iteración de tipo:
 * 
 * foreach($mode->fields as $field){
 *  $model->{$field} = Request->input($field);
 * }
 * $model->save();
 * 
 */
interface EditableFieldsIF{
   
    public function fields();
    
}
