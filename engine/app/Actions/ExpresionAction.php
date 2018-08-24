<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Actions;

use Illuminate\Support\Facades\Log;
use App\ActivityInstance;
/**
 * Description of ExpresionAction
 *
 * @author msilva
 */
class ExpresionAction extends WorkflowAction{
    //put your code here
    
    public function execute($variables){
        Log::debug("###################################");
//        $asignaciones = $this->config['assign'];
//        foreach($asignaciones as $asignacion ){
//            $var = $this->findVariable($asignacion);
//            $var['value'] = $this->evaluateExpresion($this->findVariable($asignacion));
//        }
//        
        return null;
    }
    
    private function evaluateExpresion($exp){
        return 1;
    }
    
    private function findVariable($var_name){
        return array("value" => "");
    }

    public function configParamenters() {
        return [ "assign" => [  "variable_name" => "array",
                                "struct" => [ "variable_name" => "variable", 
                                           "varaible_value" => "value" ]]
                ];
    }

}
