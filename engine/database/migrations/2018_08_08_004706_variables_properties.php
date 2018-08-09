<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VariablesProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        //
        
        DB::beginTransaction();
        Schema::table('process_variables',function(Blueprint $table){
            $table->unique(['name','id_process'],'name_process_unique');
            $table->string('type')->default('string');
            $table->foreign('id_process')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
        });
        
        Schema::table('activity_variables',function(Blueprint $table){
            $table->unique(['name','id_activity'],'name_activity_unique');
            $table->string('type')->default('string');
            $table->foreign('id_activity')
                    ->references('id')
                    ->on('activities')
                    ->onDelete('cascade');
        });
        
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        
        Schema::table('process_variables',function(Blueprint $table){
           $table->dropUnique('name_process_unique');
           $table->dropColumn('type');
           $table->dropForeign('process_variables_id_process_foreign');
        });
        
        Schema::table('activity_variables',function(Blueprint $table){
            $table->dropUnique('name_activity_unique');
            $table->dropColumn('type');
            $table->dropForeign('activity_variables_id_activity_foreign');
        });
        
        
    }
}
