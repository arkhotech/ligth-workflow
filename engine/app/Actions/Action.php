<?php

namespace App\Actions;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class Action {
    
    protected $type;
    
    protected $name;
    
    public function getName(){
        return $this->name;
    }
    
    public function getType(){
        return $this->type;
    }
    
    public abstract function exectute($params);
    
    
}