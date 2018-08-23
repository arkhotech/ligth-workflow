<?php
//declare(strict_types = 1);
namespace App\Actions;

use App\ActionInstance;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class WorkflowAction {
    
    protected $type;
    
    protected $name;
    
    public function getName(){
        return $this->name;
    }
    
    public function getType(){
        return $this->type;
    }
    /**
     * 
     */
    
    public abstract function execute($params, $variables);
    
    
}