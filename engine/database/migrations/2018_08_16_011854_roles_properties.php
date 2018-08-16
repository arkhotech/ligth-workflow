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
//            $table->foreign('roles_id')
//                    ->references('roles')
//                    ->on('id')
//                    ->onDelete('cascade');
//            $table->foreign('process_id')
//                    ->references('processes')
//                    ->on('id')
//                    ->onDelete('cascade');
        });
        
        Schema::create('activity_roles',function(Blueprint $table){
            $table->unsignedInteger('process_id');
            $table->unsignedInteger('roles_id');
//            $table->foreign('roles_id')
//                    ->references('roles')
//                    ->on('id')
//                    ->onDelete('cascade');
//            $table->foreign('process_id')
//                    ->references('processes')
//                    ->on('id')
//                    ->onDelete('cascade');
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
