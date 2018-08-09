<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VariablesInstances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        
        Schema::create('activity_vars_instances',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('id_activity_var');
            $table->string('name');
            $table->string('value')->nullable();
            $table->string('type');
            $table->json('jsonValue')->nullable();
            $table->foreign('id_activity_var')
                    ->references('id')
                    ->on('activity_variables')
                    ->onDelete('cascade');
        });
        Schema::create('process_vars_instances',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('id_process_var');
            $table->string('name');
            $table->string('value')->nullable();
            $table->json('jsonValue')->nullable();
            $table->string('type');
             $table->foreign('id_process_var')
                    ->references('id')
                    ->on('process_variables')
                    ->onDelete('cascade');
            
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
        Schema::dropIfExists('process_vars_instances');
        
        Schema::dropIfExists('activity_vars_instances');
    }
}
