<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ActivityException extends Exception{
    
    public function ActivityException($message){
        parent::__construct($message);
    }
}

