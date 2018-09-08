<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function receiveWebhook(Request $request,$id_hook){
        
        event(new WebhookRequest($request->getBody(),$id_hook));
        return response(202);
        //ubicar la actividad que recibe el webhook
        
        
    }
}
