<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeclaredVariable extends Model
{
   public function process(){
       return $this->belongsTo("App\Process");
   }
}
