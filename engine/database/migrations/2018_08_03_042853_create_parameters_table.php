<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_variables',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('id_activity');
            $table->string('name');
            $table->json('value');
            $table->string('script')->nullable();
            $table->unique('id','name');

        });
        Schema::create('process_variables',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('process_id');
            $table->string('name');
            $table->json('value');
            $table->string('script')->nullable();
            $table->unique('id','name');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_variables');
        Schema::dropIfExists('process_variables');
    }
}
