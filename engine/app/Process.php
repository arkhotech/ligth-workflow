<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Process extends Model{
    
    const fields = ['name','domain_id','role_owner_id'];
    //
    public function domain(){
        return $this->belongsTo("App\Domain");
    }
}
