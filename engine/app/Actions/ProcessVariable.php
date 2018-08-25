<?php
namespace App\Actions;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
interface ProcessVariable{
    
    public function getName();
    
    public function getValue();
    
    public function setValue($value);
    
    public function setName($value);
    
    public function saveVar();
    
}
