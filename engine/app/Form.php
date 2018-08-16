<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    //
    public function fields(){
        return $this->hasMany('App\Field');
    }
}
