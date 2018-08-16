<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Actions;

use Illuminate\Support\Facades\Log;

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
            //Si esta en el final, entonces no se hace nada
            Log::debug("-----------------");
            if($actualNode->getNextId()  == null ){
                return false;
            }
            Log::debug("Next ID: ".$actualNode->getNextId());
            //Obtiene una copia de los nodos next y prev
            $prev_action = $actualNode->getPrevNode();
            $next_action = $actualNode->getNextNode();
            //
            $sub_next_node = $next_action->getNextNode();
            //El swap    
            $actualNode->setNextId($next_action->getNextId());
            $next_action->setNextId($actualNode->getNodeId());
            $prev_id = $actualNode->getPrevId();
            $actualNode->setPrevId($next_action->getNodeId());
            $next_action->setPrevId($prev_id);
                
            if( $prev_action != null){
                    $prev_action->setNextId($next_action->getNodeId());
                    $prev_action->saveMove();
            }
            //Actualizar el subsiguiente
            if($sub_next_node != null ){
                $sub_next_node->setPrevId($actualNode->getNodeId());
                $sub_next_node->save();
            }
            
            $next_action->saveMove();
                
            
            $actualNode->saveMove();
            return true;
             
        }
        return false;
    }
    
}