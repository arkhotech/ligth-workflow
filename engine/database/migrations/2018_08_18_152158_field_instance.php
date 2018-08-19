<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FieldInstance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
       Schema::create('field_instances', function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('value')->nullable();
            $table->unsignedInteger('field_id');
            $table->unsignedInteger('form_instance_id');
            $table->unique(['name','form_instance_id']);
            $table->foreign('form_instance_id')
                    ->references('id')
                    ->on('form_instances')
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
        Schema::dropIfExists('field_instances');
    }
}
