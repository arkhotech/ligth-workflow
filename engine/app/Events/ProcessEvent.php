<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Events;

use App\ProcessInstance;
use Exception;
/**
 * Description of ProcessEvent
 *
 * @author msilva
 */
class ProcessEvent{
    
    private $source;
    
    private $event;
    
    public function __construct(ProcessInstance $source, $event) {
        $this->source = $source;
        $this->event = $event;
        
        $source->process_state = Events::FINISHED;
        $source->save();
    }
    
    public function getSource(){
        return $this->source;
    }
    
    public function getEvent(){
        return $this->event;
    }
    
}
