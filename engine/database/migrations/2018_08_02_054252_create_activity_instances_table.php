<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('process_instance_id');
            $table->unsignedInteger('activity_id');
            $table->foreign('activity_id')
                    ->references('id')
                    ->on('activities')
                    ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_instances',function(Blueprint $table){
            $table->dropForeign('activity_instances_activity_id_foreign');
        });
        Schema::dropIfExists('activity_instances');
    }
}
