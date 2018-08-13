<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Actions;

class LinkedListHelper {
    /**
     * 
     * @param \App\Actions\DoubleLinkedIF $actualNode
     * @return boolean Verdadero si la operación se llevo a cabo. 
     * Si no se puede realizar la operacion false.
     */
    public static function moveUp(DoubleLinkedIF $actualNode){
        
        if( $actualNode != null ){
            
            if($actualNode->getPrevId()  == null ){
                return false;
            }
            $prev_action = $actualNode->getPrevNode();
            $next_action = $actualNode->getNextNode();
            //El swap
            //Contextar anterior con siguiente
            $prev_id = $prev_action->getPrevId();
            //Sobrescribir el anterior completo
            $prev_action->setNextId($actualNode->getNextId());
            $prev_action->setPrevId($actualNode->getNodeId());
            //Subir el actual
            $actualNode->setNextId($prev_action->getNodeId());
            $actualNode->setPrevId($prev_id);
            //actualizar el siguiente
            if( $next_action != null ){
                $next_action->setPrevId($prev_action->getNodeId());
                $next_action->saveMove();
            }
            
            $prev_action->saveMove();
            $actualNode->saveMove();
            return true;
             
        }
        return false;
    }
    
    public static function moveDown(DoubleLinkedIF $actualNode){
        if( $actualNode != null ){
            
            if($actualNode->getNextId()  == null ){
                return false;
            }
            $prev_action = $actualNode->getPrevNode();
            $next_action = $actualNode->getNextNode();
            //El swap
            //Contextar anterior con siguiente
            $prev_id = $next_action->getPrevId();
            //Sobrescribir el anterior completo
            $next_action->setNextId($actualNode->getNextId());
            $next_action->setPrevId($actualNode->getNodeId());
            //Subir el actual
            $actualNode->setNextId($next_action->getNodeId());
            $actualNode->setPrevId($prev_id);
            //actualizar el siguiente
            if( $prev_action != null ){
                $prev_action->setPrevId($next_action->getNodeId());
                $prev_action->saveMove();
            }
            
            $next_action->saveMove();
            $actualNode->saveMove();
            return true;
             
        }
        return false;
    }
    
}