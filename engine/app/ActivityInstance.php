<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityInstance extends Model{
    
    
    public function activities(){
        return $this->belongsTo("\App\Activity");
    }
}
