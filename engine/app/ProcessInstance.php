<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessInstance extends Model{
    
    public function __construct($listener = null){
        
    }
    
    public function process(){
        return $this->belongsTo("App\Process");
    }
    //
    
    public function startProcess(){
        
    }
    
    public function stopProcess(){
        
    }
}
