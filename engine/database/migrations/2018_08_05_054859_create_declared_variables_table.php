<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeclaredVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('declared_variables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('process_id');
            $table->string('scope')->default('PROCESS');
            $table->string('name');
            $table->string('type')->default('string');
            $table->string('validator')->defualt('Is_String');
            $table->timestamps();
            $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
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
        Schema::dropIfExists('declared_variables');
    }
}
