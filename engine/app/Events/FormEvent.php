<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\FormInstance;
use Illuminate\Support\Facades\Log;

class FormEvent{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $form_instance;
    
    const FINISH = 1;
    
    const START=0;
    
    private $event;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(FormInstance $form, $event){
        $this->form_instance = $form;
        $this->event = $event;
        //
    }

    public function getSourceForm(){
        return $this->form_instance;
    }
    
    public function getEvent(){
        return $this->event;
    }
//    /**
//     * Get the channels the event should broadcast on.
//     *
//     * @return \Illuminate\Broadcasting\Channel|array
//     */
//    public function broadcastOn()
//    {
//        return new PrivateChannel('channel-name');
//    }
}
