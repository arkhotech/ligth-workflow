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
    
    public function onEntry();
    
    public function onExit();
    
    public function onActivity();
    
}
