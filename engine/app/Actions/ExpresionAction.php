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
    
    public function __construct(){
        
    }
    
    public function execute($params){
        Log::debug("###################################");
        $x = $params;
        return null;
    }
    
    public function analice(){
                
    }
    
}
