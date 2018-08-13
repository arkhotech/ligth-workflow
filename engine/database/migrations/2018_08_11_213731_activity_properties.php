<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ActivityProperties extends Migration
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
            $table->unsignedInteger('id_next_action')->nullable();
            $table->unsignedInteger('id_prev_action')->nullable();
            $table->unsignedInteger('type');
            if( Schema::hasColumn('actions','comand') ){
                $table->renameColumn('comand','command');
            }
            $table->string('command')->nullable()->change();
            $table->unsignedInteger('id_activity');
            $table->foreign('id_activity')
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
        //
        Schema::table('actions',function(Blueprint $table){
            $table->dropColumn('id_next_action');
            $table->dropColumn('id_prev_action');
            $table->dropColumn('type');
            $table->dropForeign('actions_id_activity_foreign');
            $table->dropColumn('id_activity');
        });
    }
}
