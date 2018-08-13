<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Actions;

/**
 * Description of LinkedExecutionHandler
 *
 * @author msilva
 */
class LinkedExecutionHandler {
    //put your code here
    public static function executeChain(LinkedExecution $chain){
        while($chain != null ){
            $chain->execute();
            $chain = $chain->next();
        }
    }
}
