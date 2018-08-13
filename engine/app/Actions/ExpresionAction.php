<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Actions;

/**
 * Description of ExpresionAction
 *
 * @author msilva
 */
class ExpresionAction extends Action{
    //put your code here
    
    public function __construct(){
        
    }
    
    public function execute(Array $params = null) {
        //$parser = new Parsers\ParensParser();
        //$parser->parse("((1+2)*(1+3)*(4/5) * 5)");
        eval("((1+2)*(1+3)*(4/5) * 5)");
        return null;
    }
    
    public function analice(){
                
    }
    
}
