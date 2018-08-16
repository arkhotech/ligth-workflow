<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoleUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('roles',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('description');
        });
        Schema::create('roles_users',function(Blueprint $table){
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('role_id');
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
        Schema::dropIfExists('roles');
        Schema::dropIfExits('roles_users');
    }
}
