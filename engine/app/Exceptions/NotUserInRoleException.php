<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Exceptions;

use Exception;
/**
 * Description of NotUserInRoleException
 *
 * @author msilva
 */
class NotUserInRoleException extends Exception{
    //put your code here
      public function NotUserInRoleException($message){
        parent::__construct($message);
    }
}
