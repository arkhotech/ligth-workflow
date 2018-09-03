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
        Log::info("iniciando proceso");
        $source = $event->getSource();
        switch($event->getEvent()){
            case Events::NEW_INSTANCE:
                $source->init();
            case Events::ON_ACTIVITY:
                $source->start();
        }
    }
    
   
    
   
    
    private function checkDefault($transitions){
        foreach($transitions as $transition ){
            if($transition->default){
                return $transition;
            }
        }
        return null;
    }
    
}