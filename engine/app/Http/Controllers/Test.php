<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use App\Actions\ActionFactory;
use App\Form;
/**
 * Description of Test
 *
 * @author msilva
 */
class Test extends Controller{
    //put your code here
    
    public function test(){
        return response()->json(array("mensaje" => "esto es una prueba"),200);
    }
    
}
