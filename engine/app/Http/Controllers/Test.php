<?php
namespace App\Http\Controllers;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Process;
/**
 * Description of Test
 *
 * @author msilva
 */
class Test extends Controller{

    public function test(){
        
        $assg = [
            "assignations" => [
                    [
                     "to" =>   [ "varname" => "t3", "type" => "string"] ,
                     "from" => [ "expr" => "12" , "type" => "string"]
                    ],
                    [
                     "to" =>   [ "varname" => "p1", "type" => "integer"] ,
                     "from" => [ "expr" => "5 + 5 + strlen(@@t3)" , "type" => "integer"]
                    ],
            ]];
        
        $subject = "@@p1 + @@p2";
        $result = array();
        preg_match_all("/@@([\w])+[\d\w]*/", $subject, $result);
        
        preg_replace("/@@p1/", 5, $subject);       
        
        
        $assign_action_class = config('actions.assign');
        
        $process = Process::find(2);
        $activity = $process->activities()->find(10);
        $act_instance = $activity->activities()->first();
        echo $act_instance->id;
        $action = new $assign_action_class($assg,array(),$act_instance);
        
        $action->execute(null);
        
        return response()->json(["result" => "Ok"],200);
        
        
        
        /*
        
        $p = Process::with('declaredVariables')
                ->with(['activities' => function($query){
                    $query->with('outputTransitions');
                    $query->with('actions');
                }])
                
                ->find(6);
        
        return response()->json($p);
         * 
         */
        
    }
    
}
