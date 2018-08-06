<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityInstance extends Model{
    
    
    public function activity(){
        return $this->belongsTo("\App\Activity");
    }
    
    public function variables(){
        return $this->many("App\ActivityVarible");
    }
    
    public function process(){
        return $this->belongTo("App\ProcessInstance"); 
    }
}
