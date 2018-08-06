<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProcessCursor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('process_instances',function(Blueprint $table){
            $table->unsignedInteger('activityCursor')->after('id');
            $table->enum('state',['active','finished','error']);
        });
        Schema::table('activities',function(Blueprint $table){
            $table->boolean('start_activity');
            $table->boolean('end_activity');
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
            $table->dropColumn('activityCursor');
            $table->dropColumn('state');
        });
        Schema::table('activities',function(Blueprint $table){
            $table->dropColumn('start_activity');
            $table->dropColumn('end_activity');
        });
    }
}
