<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model{
    //
    const editable_fields = [ 'name'];
    
    public function activities(){
        return $this->hasMany("\App\ActivityInstance");
    }
    
    public function process(){
        return $this->belongsTo('\App\Process');
    }
    
    public function createInstance(ProcessInstance $process){
        if($this->process_id != $process->process_id){
            return null;
        }
        
    }
}
