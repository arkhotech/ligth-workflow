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
    
    const ACTION_PATTERN = "/@#[\w+\S]+[.\w+\S]*/";  //ACtion vars
    
    const GLOBAL_PATTERN = "/#[\w+\S]+[.\w+\S]*/";
    
    const ACTIVITY_PATTERN = "/@[\w+\S]+[.\w+\S]*/";
    
    public function execute($variables){
        Log::debug("###################################");
        
        foreach($this->config["assign"] as $item ){  //par variable destino <--  variable desde
            ;  //Variable a asignar 
              
            $this->replaceExpr($item['to_var'], $item['from_var'],$variables);
            //Se debe buscar la variable a la que apunta a la variable reigistrada
            //$this->replaceExpr($exp["value"],$variables);
            //$value = $variables[$exp["varname"]];

        }
        
        return null;
    }
    
    private function replaceExpr($to,$from,$source_varmap){
        preg_match(self::ACTION_PATTERN, $from,$out);
        if(count($out) > 0 ){
            $val = $out[0];
            $var_path = explode('.',str_replace('@#', "", $val));
            if(key_exists($var_path[0], $source_varmap)){
                
            }
            $source_varmap[$var_path[0]];
            Log::debug("Variable: ".$var_path[0]);
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
                                   "value" => "pattern" ]]
                ];
    }

}
