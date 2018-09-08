<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;


class WebhooksController extends Controller{
    
    public function receiveWebhook(Request $request,$id_hook){
        
//        $process = Process::where('id',$id_hook)        
//                ->first();  //ubicar el proceso
        event(new WebhookRequest($request->getBody(),$id_hook));
        return response(202);
        //ubicar la actividad que recibe el webhook
        
        
    }
    
}