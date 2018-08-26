<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Fields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('fields',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('type');
            $table->string('validation')->nullable();
            $table->string('value')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('form_id');
            $table->unique(['form_id','name']);
            $table->unsignedInteger('prev_field')->nullable();
            $table->unsignedInteger('next_field')->nullable();
            $table->unsignedInteger('enabled')->default(1);
            $table->unsignedInteger('output_field')->default(1);
            $table->foreign('form_id')
                    ->references('id')
                    ->on('forms')
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
        Schema::dropIfExists('fields');
    }
}
