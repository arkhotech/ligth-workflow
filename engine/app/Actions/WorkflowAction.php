<?php
//declare(strict_types = 1);
namespace App\Actions;

use App\ActionInstance;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class WorkflowAction{
    
    protected $type;
    
    protected $name;
    
    protected $config;
    
    protected $variables;
    
    protected $activity_instance;
    
    public function __construct($config,$variables,$activity_instance = null) {
        $this->config = $config;
        $this->variables = $variables;
        $this->activity_instance = $activity_instance;
    }
    
    public function getName(){
        return $this->name;
    }
    /**
     * 
     * @return type
     */
    public function getType(){
        return $this->type;
    }
    
    /**
     * Pasar un lista de variables dentro del contexto de ejecución.
     */
    public abstract function execute( $variables);
    
    public abstract function configParamenters();
}