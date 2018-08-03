<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityInstance extends Model{
    
    
    public function activities(){
        return $this->belongsTo("\App\Activity");
    }
    
    public function parameters(){
        return $this->many("App\ActivityParameter");
    }
}
