<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Actions;

use Illuminate\Support\Facades\Log;
use App\Variable;
use Exception;
/**
 * Description of AssignAction
 *
 * @author msilva
 */
class AssignAction extends WorkflowAction{
    
    const EXPR = "/@@[a-zA-Z|_]{1}[\da-zA-Z]*(\.[\w\d_-]+)*/";
    
    public function __construct($config, $variables, $activity_instance = null) {
        parent::__construct($config, $variables,$activity_instance);  
    }
    
    
    public function configParamenters() {
        return [ "assignations" => [  
                       [ 
                           "to_varname" => [ "type" => "string" ],
                            "from"  => ["type" => "text" ]
                       ],
                       [
                           "to_varname" => [ "type" => "string"  ],
                           "from" => [ "type" => "json"]
                       ]
                    ]
            ];
    }

    public function execute($variables) {
        $vars = $this->getListVariables();
//        var_dump($vars);die;
        $this->replaceExpressions($vars);
    }
    
    private function replaceExpressions($vars){
        foreach($vars as $var ){
            //Receuperar variables desde la expresión (REsult es la lista de variables)
            preg_match_all(self::EXPR, $var['expr'],$result);
            
            $new_expresion = $this->replaceVariables($result,$var['expr']);
            
            $final_value = $this->execEval($new_expresion);
            $var['to_var']->value = $final_value;
            $var['to_var']->save();
        }
    }
    
    private function getVariableValue(String $varname){
        
        if($varname === null || $varname === ""){
            return null;
        }
        
        Log::debug($varname);
        
        $new_varname = str_replace("@@", "" , $varname);
        Log::debug($new_varname);
        //buscar definicion
        $process_instance = $this
                ->activity_instance
                ->processInstance()
                ->first();
        $process = $process_instance->process()->first();
        //Buscar la definicion de la variable, sino existe, entoncs por ahora error
        $var_found =  $process->declaredVariables()
                ->where("name",$new_varname)
                ->first();
        
        if($var_found !== null ){
            
            $var_instance =  ( $var_found->existsInstance($process_instance) === null )
                    ? $var_found->createInstance($process_instance)
                    : $var_found->existsInstance($process_instance);
            
            if( $var_instance->value === null || $var_instance->value === ""){
                return ($var_instance->type === "string" ) ? "''" : 0;
            }else{
                return $var_instance->value;
            }
        }else{
            throw new Exception("Variable not found [".$varname."]");
        }
        
        return null;
    }
    
    private function replaceVariables(array $varlist, &$expresion){
        if($varlist === null){
            return $expresion;
        }else{
           
            foreach($varlist as $var){
                if( empty($var) ){
                    continue;
                }

                $value = $this->getVariableValue($var[0]);
                $expresion = str_replace($var[0], $value , $expresion);
            }
           ;
        }
        return $expresion;
    }
    /**
     * Retorna una lista de items  item.var, item.expr
     * @return type
     */
    private function getListVariables(){
        $process_instance =  $this->activity_instance->process()->first();
        $result = array();
        foreach($this->config["assignations"] as $arr_item){
            $to = (object) $arr_item["to"];
            $from = (object) $arr_item["from"];
            $var_inst = $process_instance
                    ->variables()
                    ->where(array('name'=>$to->varname))
                    ->first();
            
            if($var_inst === null){ 
                continue;
            }else{
                $result[] = array("to_var" => $var_inst, "expr" => $from->expr);
            }
        }
        return $result;
    }
    
    private function execEval($in_exp){

        // Remove whitespaces
        $expresion = preg_replace('/\s+/', '', $in_exp);

        $number = '(?:\d+(?:[,.]\d+)?|pi|π)'; // What is a number
        $functions = '(?:sinh?|cosh?|tanh?|abs|acosh?|asinh?|atanh?|exp|log10|deg2rad|rad2deg|sqrt|ceil|floor|round)'; // Allowed PHP functions
        $operators = '[+\/*\^%-]'; // Allowed math operators
        $regexp = '/^(('.$number.'|'.$functions.'\s*\((?1)+\)|\((?1)+\))(?:'.$operators.'(?2))?)+$/'; // Final regexp, heavily using recursive patterns
        
        //var_dump($expresion);die;
        //if (preg_match($regexp, $expresion)){
            eval('$result = '.$expresion.';');
        /*}else{
            $result = false;

        }*/
        return $result;
    }
    
    
//put your code here
}
