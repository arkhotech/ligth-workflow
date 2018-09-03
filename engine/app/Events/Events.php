<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Events;

interface Events{
    
    const NEW_INSTANCE = 0;
    
    const IDLE = 0;
    
    const ON_ENTRY=1;
    
    const ON_ACTIVITY = 2;
    
    const ON_EXIT = 3;
    
    const PENDDING = 4;
    
    const FINISHED = 5;
    
    const ERROR = 6;
}