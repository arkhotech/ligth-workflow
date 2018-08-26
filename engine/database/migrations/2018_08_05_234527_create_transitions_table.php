<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transitions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('order')->default(0);
            $table->string('description')->nullable();
            $table->unsignedInteger('prev_activity_id');
            $table->unsignedInteger('next_activity_id');
            $table->string('condition');
            $table->timestamps();
            $table->foreign('prev_activity_id')
                    ->references('id')
                    ->on('activities')
                    ->onDelete('cascade');
            $table->foreign('next_activity_id')
                    ->references('id')
                    ->on('activities')
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
        Schema::dropIfExists('transitions');
    }
}
