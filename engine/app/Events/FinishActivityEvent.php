<?php

namespace App\Events;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\ActivityInstance;
use Illuminate\Support\Facades\Log;
/**
 * Description of FinishStageEvent
 *
 * @author msilva
 */
class FinishActivityEvent {
    //put your code here
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $instance;
    
    const FINISH = 1;
    
    const START=0;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ActivityInstance $instance){
        $this->nstance = $instance;
        //
    }

    public function getActivityInstance(){
        return $this->instance;
    }
    
    public function getEvent(){
        return $this->event;
    }
}
