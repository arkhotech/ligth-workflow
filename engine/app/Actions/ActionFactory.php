<?php

namespace App\Actions;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Illuminate\Support\Facades\Config;

class ActionFactory{
    
    public static function create($type = "EXPRESION"){
        echo "activity.".$type;
        $source = Config::get("activity.".$type);
        $action = new $source;
        return $action;
    }
    
}