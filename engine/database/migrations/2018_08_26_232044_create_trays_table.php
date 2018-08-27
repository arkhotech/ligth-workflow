<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTraysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trays', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('tray_name');
            $table->string('description');
            $table->unique('tray_name');
        });
        
         Schema::create('role_tray', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('tray_id');
            $table->unique(['role_id','tray_id']);
            $table->foreign('role_id')
                    ->references('id')
                    ->on('trays')
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
        Schema::dropIfExists('role_tray');
        Schema::dropIfExists('trays');
    }
}
