<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ControlTransiciones extends Migration{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("transitions",function(Blueprint $table){
            $table->boolean("default")->default(false);
        });
        Schema::table("processes",function(Blueprint $table){
            $table->boolean("asynch")->default(false);
        });
        Schema::table("process_instances",function(Blueprint $table){
            $table->boolean("asynch")->default(false);
        });
        Schema::table("activity_variables",function(Blueprint $table){
            $table->dropColumn('value');
            $table->dropColumn('script');
        });
        Schema::table("process_variables",function(Blueprint $table){
            $table->dropColumn('value');
            $table->dropColumn('script');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("transitions",function(Blueprint $table){
            $table->dropColumn('default');
        });
         Schema::table("processes",function(Blueprint $table){
            $table->dropColumn("asynch");
        });
        Schema::table("process_instances",function(Blueprint $table){
            $table->dropColumn("asynch");
        });
        Schema::table("activity_variables",function(Blueprint $table){
            $table->string('value');
            $table->string('script');
        });
        Schema::table("process_variables",function(Blueprint $table){
            $table->string('value');
            $table->string('script');
        });
    }
}
