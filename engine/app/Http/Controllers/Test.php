<?php
namespace App\Http\Controllers;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\ActionInstance;
use App\Actions\ExpresionAction;
use App\User;
use Adldap\Laravel\Facades\Adldap;
/**
 * Description of Test
 *
 * @author msilva
 */
class Test extends Controller{
    //put your code here
    
    public function test(){
        
        $user = Adldap::search()->users()->find("msilva@arkhotech.com");
        
        //$user=User::where("email","msilva@arkhotech.com")->first();
        
//        preg_match(, "@#Action.result",$out);
//        var_dump($out);
//        $config = array("assign" => array(array("to_var"=> "p1","from_var" => "@#Action1.result")) );
//        
//        $expresion = new ExpresionAction($config);
//        $actions = ActionInstance::selectRaw("name, output as value")->get();
//        $vars = array();
//        foreach($actions as $action){
//            $vars[$action->name] = $action;
//        }
//        //$var = array("p1" => array("name"=>"p1","value"=>""));
//        $expresion->execute($vars);
//        
        return response()->json($user);
    }
    
}
