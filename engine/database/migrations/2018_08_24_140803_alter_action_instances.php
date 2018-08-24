<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterActionInstances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::disableForeignKeyConstraints();
        Schema::table('action_instances',function(Blueprint $table){
            $table->unsignedInteger('activity_instance_id');
            $table->foreign('activity_instance_id')
                    ->references('id')
                    ->on('activity_instances')
                    ->onDelete('cascade');
            $table->string('name');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::disableForeignKeyConstraints();
        Schema::table('action_instances',function(Blueprint $table){
            $table->dropForeign('action_instances_activity_instance_id_foreign');
            $table->dropColumn('activity_instance_id');
            $table->dropColumn('name');
        });
        Schema::enableForeignKeyConstraints();
    }
}
