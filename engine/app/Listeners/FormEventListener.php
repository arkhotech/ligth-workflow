<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\FormEvent;
use App\Events\FinishActivityEvent;
use App\Stage;
use Illuminate\Support\Facades\Log;
use App\Events\ActivityEvents;

class FormEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    public function onFinishActivityEvent(FinishActivityEvent $event){
        $activity_instance = $event->getActivityInstance();
        Log::debug("Ejecutando la salida");
        $activity_instance->onExit();
        if($activity_instance->end_activity){
           $process_instance = $activity_instance->process();
           $process_instance->state = ActivityEvents::PENDDING;
           $process_instance->save();
        }
    }
    
    public function onFormEvent(FormEvent $event){
        switch($event->getEvent()){
            case FormEvent::FINISH:
                Log::info("Stage finalizado");
                //ir a la siguiente etapa
                $form_instance = $event->getSourceForm();
                $stg_instance = $form_instance->stageInstance()->first();
                //$stg_instance->nextStage();
                        
        }
        Log::debug("Evento recibido");
    }
    
    public function subscribe($events){
        
        $events->listen(
                "App\Events\FormEvent",
                "App\Listeners\FormEventListener@onFormEvent"
                );
        $events->listen(
                "App\Events\FinishActivityEvent",
                "App\Listeners\FormEventListener@onFinishActivityEvent"
                );
        
    }
}