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
        $p = Process::with('declaredVariables')
                ->with(['activities' => function($query){
                    $query->with('outputTransitions');
                    $query->with('actions');
                }])
                
                ->find(6);
        
        return response()->json($p);
    }
    
}
