<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ParallelActivityModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('activities',function(Blueprint $table){
            $table->dropColumn('type');
        });
        
        Schema::table('activities',function(Blueprint $table){
            $table->string('type')->default('activity');
        });
                 
        Schema::table('activity_instances',function(Blueprint $table){
            $table->uuid('flow_path_id')->nullable();
        });
        
        Schema::table('transitions',function(Blueprint $table){
            $table->uuid('flow_path_id')->nullable();
        });
        
        Schema::table('process_instances',function(Blueprint $table){
            $table->json('meta_data')->nullable();
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
         Schema::table('activity_instances',function(Blueprint $table){
            $table->dropColumn('flow_path_id');
        });
        
        Schema::table('transitions',function(Blueprint $table){
            $table->dropColumn('flow_path_id');;
        });
        
        Schema::table('process_instances',function(Blueprint $table){
            $table->dropColumn('meta_data');
        });
    }
}
