<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StageInstances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
          Schema::create('stage_instances',function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('activity_instance_id');
//            $table->unsignedInteger('next_stage')->nullable();
//            $table->unsignedInteger('prev_stage')->nullable();
            $table->unsignedInteger('type');
            $table->foreign('activity_instance_id')
                    ->references('id')
                    ->on('activity_instances')
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
        Schema::dropIfExists('stage_instances');
    }
}
