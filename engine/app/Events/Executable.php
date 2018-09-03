<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Events;
use Exception;
/**
 * Description of Executable
 *
 * @author msilva
 */
interface Executable {
    
    public function init();
    
    public function end();
    
    public function start();
    
    public function handleError(Exception $e);
}
