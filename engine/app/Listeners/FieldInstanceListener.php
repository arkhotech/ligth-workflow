<?php

namespace App\Listeners;

use App\FieldInstance;
use Illuminate\Support\Facades\Log;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FieldInstanceListener{
    
    public function saving(FieldInstance $field){
        Log::info("Guardarndo: ".$field->name);
    }
    
    public function saved(FieldInstance $field){
        Log::info("Registro guardado: ".$field->name);
    }
    
}