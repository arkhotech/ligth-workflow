<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Actions;
use App\FieldInstance;
use Illuminate\Support\Facades\Log;
/**
 * Description of newPHPClass
 *
 * @author msilva
 */
class FieldEventListener {
    //put your code here
    
    public function saving(FieldInstance $field){
        Log::debug("Campo guardado !!!!");
        //$field->validate();
        return false;
    }
}
