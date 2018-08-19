<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RolesProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('process_roles',function(Blueprint $table){
            $table->unsignedInteger('process_id');
            $table->unsignedInteger('roles_id');
            $table->timeStamps();
            $table->unique(['process_id','roles_id']);
        });
        
        Schema::create('activity_roles',function(Blueprint $table){
            $table->unsignedInteger('activity_id');
            $table->unsignedInteger('role_id');
            $table->timeStamps();
            $table->unique(['activity_id','role_id']);
        });
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('process_roles');
        Schema::dropIfExists('activity_roles');
        
    }
}
