<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('state');
            $table->unsignedInteger('process_id');
            $table->timestamps();
            $table->foreign('process_id')->references('id')->on('users');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('process_instances',function(Blueprint $table){
           $table->dropForeign('process_instances_process_id_foreign');
        });
        Schema::dropIfExists('process_instances');
    }
}
