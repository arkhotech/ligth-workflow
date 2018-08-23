<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterActionModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('actions',function(Blueprint $table){
           $table->dropForeign('actions_id_activity_foreign');
           $table->dropColumn('id_activity');
  
           $table->unsignedInteger('activity_id');
           $table->string('class');
           $table->json('config');
           $table->string('name')->after('id');
           $table->string('description')->after('name')->nullable();
           $table->foreign('activity_id')
                   ->references('id')
                   ->on('activities')
                   ->onDelete('cascade');
        });
        Schema::create('action_instances',function(Blueprint $table){
            $table->timestamps();
            $table->increments('id');
            $table->string('class');
            $table->json('config');
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->json('exception')->nullable();
            $table->unsignedInteger('action_id');
            $table->unsignedInteger('action_status')->default(0);
            $table->datetime('fecha_ejecucion')->nullable();
            $table->foreign('action_id')
                    ->references('id')
                    ->on('actions')
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
        Schema::table('actions',function(Blueprint $table){
            $table->dropColumn('config');
            $table->dropColumn('description');
            $table->dropColumn('name');
            $table->dropColumn('class');
            $table->dropForeign('actions_activity_id_foreign');
            $table->dropColumn('activity_id');
            $table->unsignedInteger('id_activity');
            $table->foreign('id_activity')
                    ->references('id')
                    ->on('activities')
                    ->onDelete('cascade');
        });
        Schema::dropIfExists('action_instances');
    }
}
