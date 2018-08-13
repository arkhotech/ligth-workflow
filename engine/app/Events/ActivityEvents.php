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
interface ActivityEvents {
    //put your code here
    
    const EVENTS = ["ON_ENTRY","ON_ACTIVITY", "ON_EXIT"];
    
    const IDLE = 0;
    
    const ON_ENTRY=1;
    
    const ON_ACTIVITY = 2;
    
    const ON_EXIT = 3;
    
    const PENDDING = 4;
    
    const FINISHED = 5;
    
    const ERROR = 6;
    
    public function onEntry();
    
    public function onExit();
    
    public function onActivity();
    
}
