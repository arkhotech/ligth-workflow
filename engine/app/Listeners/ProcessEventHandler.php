<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Listeners;

use App\Events\Events;
use App\Events\ProcessEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessEventHandler { //implements ShouldQueue{
    
    public $queue = "engine";
    
    public function __construct(){
        
    }
    
    public function handle(ProcessEvent $event){
        Log::info("iniciando proceso");
        try{
            $source = $event->getSource();
            switch($event->getEvent()){
                case Events::IDLE:
                    Log::info("[on Activity] init");
                    //iniciar
                    $source->init();
                    break;
                case Events::ON_ACTIVITY:
                    Log::info("[on Activity] Ejecutando");
                    $source->start();
                    break;
                case Events::ON_EXIT:
                    $source->end(); 
                    break;

                case Events::FINISHED:
                    Log::info("[on Activity] Finalizando");
                    break;
            }
        }catch(Throwable $e){
            Log::error("Controlando error en proceso");
            $event->handleError($e);
        }
    }
}