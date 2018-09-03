<?php

namespace App\Events;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ActivityEvents
 *
 * @author msilva
 */
class ActivityEvent {
    
    private $source;
    
    private $event;
    
    public function __construct(Executable $source, $event) {
        $this->source = $source;
        $this->event = $event;
    }
    
    public function getSource(){
        return $this->source;
    }
    
    public function getEvent(){
        return $this->event;
    }
    
}
