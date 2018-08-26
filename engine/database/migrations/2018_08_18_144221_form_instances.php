<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FormInstances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::create('form_instances',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('stage_instance_id');
            $table->unsignedInteger('form_id');
            $table->foreign('form_id')
                    ->references('id')
                    ->on('forms')
                    ->onDelete('cascade');
            $table->foreign('stage_instance_id')
                    ->references('id')
                    ->on('stage_instances')
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
        Schema::dropIfExists('form_instances');
    }
}
