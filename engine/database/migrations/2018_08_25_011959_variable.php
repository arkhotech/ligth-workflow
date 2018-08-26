<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Variable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists('activity_variables');
        Schema::dropIfExists('activity_var_instances');
        Schema::dropIfExists('process_variables');
        Schema::dropIfExists('process_vars_instances');
        Schema::create('variables',function(Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('process_id');
            $table->string('name');
            $table->unsignedInteger('scope')->default(0); //0 global, 1 activity
            $table->string('type')->default('string');
            $table->unique(['process_id','name']);
        });
        
        Schema::create('variable_instances',function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('variable_id');
            $table->unsignedInteger('process_instance_id');
            $table->unsignedInteger('activity_instance_id');
            $table->json('value');
            $table->foreign('variable_id')
                    ->references('id')
                    ->on('variables')
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
        Schema::dropIfExists('variable_instances');
        Schema::dropIfExists('variables');
    }
}
