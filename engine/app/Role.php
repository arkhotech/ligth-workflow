<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const ADMIN_ROLE_ID = 1;
    //
    public function users(){
        return $this->belongsToMany(User::class,"roles_users");
    }
    
    public function trays(){
        return $this->belongsToMany(Tray::class);
    }
    
    public function processes(){
        return $this->belongsToMany("App\Process","process_roles");
    }
    
    public function activities(){
        return $this->belongsToMany("App\Activity","activity_roles");
    }
    
}
