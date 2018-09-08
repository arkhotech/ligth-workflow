<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebhookEvenet
 *
 * @author msilva
 */
class WebhookEvent {
    //put your code here
    private $json;
    
    private $id_hook;
    
    public function __construct($json,$id_hook) {
        $this->id_hook = $id_hook;
        $this->json = $json;
    }
    
    public function getJson(){
        return $this->json;
    }
    
    public function getIdHook(){
        return $this->id_hook;
    }
    
}
