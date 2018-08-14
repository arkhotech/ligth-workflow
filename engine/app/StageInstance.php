<?php

namespace App;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StageInstance
 *
 * @author msilva
 */
use Illuminate\Database\Eloquent\Model;
use App\Events\ActivityEvents;
use App\EditableFieldsIF;
use App\Actions\DoubleLinkedIF;


class StageInstance extends Model implements DoubleLinkedIF,  EditableFieldsIF, ActivityEvents{
    
    public function fields() {
        
    }

    public function getNextId() {
        
    }

    public function getNextNode() {
        
    }

    public function getNodeId() {
        
    }

    public function getPrevId() {
        
    }

    public function getPrevNode() {
        
    }

    public function saveMove() {
        
    }

    public function setNextId($id) {
        
    }

    public function setPrevId($id) {
        
    }

    public function onActivity() {
        
    }

    public function onEntry() {
        
    }

    public function onExit() {
        
    }

}
