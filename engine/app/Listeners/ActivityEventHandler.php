<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Listeners;

use App\Events\ActivityEvent;
use App\Events\Events;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ActivityEventHandler { //implements ShouldQueue{
    
    public $queue = "engine";
    
    public function __construct(){
        
    }
    
    public function handle(ActivityEvent $event){
        
        $source = $event->getSource();
        switch($event->getEvent()){
            case Events::NEW_INSTANCE:
                Log::info("[ActivityEventHandler] ==========  INICIANDO ACTIVIDAD  ============");
                $source->init(); break;
            case Events::ON_ACTIVITY:
                $source->start(); break;
            case Events::ON_EXIT:
                Log::info("[ActivityEventHandler] ==========  FINALIZANDO ACTIVIDAD  ============");
                $source->end(); break;
        }
    }

}