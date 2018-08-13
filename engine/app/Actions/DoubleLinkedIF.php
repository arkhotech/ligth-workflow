<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Actions;

/**
 * Description of DoubleLinkedIF
 *
 * @author msilva
 */
interface DoubleLinkedIF {
    //put your code hernodee
    public function getNodeId();
    
    public function getNextId();
    
    public function getPrevId();
    
    public function setNextId($id);
    
    public function setPrevId($id);
    /**
     * @return DoubleLinkedIF Instancia del objeto siguiente en la lista
     */
    public function getNextNode();
    /**
     * @return DoubleLinkedIF Instancia del objeto precedente en la lista
     */
    public function getPrevNode();
    /**
     * Guardar los cambios
     */
    public function saveMove();
}
