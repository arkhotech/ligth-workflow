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
        
        foreach($this->config["assign"] as $exp ){
            $this->replaceExpr($exp["value"],$variables);
            //$value = $variables[$exp["varname"]];
            Log::debug($value);
        }
        
        return null;
    }
    
    private function replaceExpr($value,$var_list){
        foreach($var_list as $var){
            Log::debug($value);
            Log::debug(strpos($value,"@#"));
            Log::debug(strpos($value, ' '));
            Log::debug(substr($value, strpos($value,"@#"), strpos($value, ' ')));
        }
    }
    
    private function evaluateExpresion($exp){
        return 1;
    }
    
    private function findVariable($var_name){
        return array("value" => "");
    }

    public function configParamenters() {
        return [ "assign" => [   [ "varname" => "variable", 
                                   "value" => "value" ]]
                ];
    }

}
