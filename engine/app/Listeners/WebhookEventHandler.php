<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Listeners;

use App\Events\WebhookEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class WebhookEventHandler implements ShouldQueue{
    
    public $queue = "webhook";
    
    public function __construct() {
        
    }
    
    public function handle(WebhookEvent $event){
        Log::debug("Recibiendo evento de Webhook: ".$event->getIdHook());
    }
    
}