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
        Schema::create('activity_parameters',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('id_activity');
            $table->string('name');
            $table->json('value');
            $table->string('script')->nullable();
            $table->unique('id','name');
            $table->foreign('id_activity')
                    ->references('id')
                    ->on('activity_instances')
                    ->onDelete('cascade');
        });
        Schema::create('process_parameters',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('id_process');
            $table->string('name');
            $table->json('parameter');
            $table->string('script');
            $table->unique('id','name');
            $table->foreign('id_process')
                    ->references('id')
                    ->on('process_instances')
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
        Schema::dropIfExists('activity_parameters');
        Schema::dropIfExists('process_parameters');
    }
}
