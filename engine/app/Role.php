<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const ADMIN_ROLE_ID = 1;
    //
    public function users(){
        return $this->belongsToMany("App\User","roles_users");
    }
    
    public function processes(){
        return $this->belognsToMany("App\Process","process_roles");
    }
    
    public function activities(){
        return $this->belognsToMany("App\Activity","activity_roles");
    }
    
}
