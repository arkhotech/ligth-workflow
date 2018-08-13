<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProcessState extends Migration
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
            $table->unsignedInteger('process_state')->default(0);
        });
        
        Schema::table('activity_instances',function(Blueprint $table){
            $table->unsignedInteger('activity_state')->default(0);           
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
        Schema::table('process_instances',function(Blueprint $table){
            $table->dropColumn('process_instances');
        });
        Schema::table('activity_instances',function(Blueprint $table){
            $table->dropColumn('activity_state');
        });
    }
}
