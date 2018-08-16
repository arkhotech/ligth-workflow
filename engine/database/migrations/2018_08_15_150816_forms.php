<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Forms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('forms',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('stage_id');
            $table->unique(['stage_id','name']);
            $table->unsignedInteger('prev_form')->nullable();
            $table->unsignedInteger('next_form')->nullable();
            $table->unsignedInteger('state')->default(0);
            $table->foreign('stage_id')
                    ->references('id')
                    ->on('stages')
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
        Schema::dropIfExists('forms');
    }
}
