<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StagesProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('stages',function(Blueprint $table){
            $table->unsignedInteger('activity_id');
            $table->unsignedInteger('next_stage')->nullable();
            $table->unsignedInteger('prev_stage')->nullable();
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('type');
            $table->string('name')->after('id');
            $table->foreign('activity_id')
                    ->references('id')
                    ->on('activities')
                    ->onDelete('cascade');
            $table->unique(['name','activity_id']);
        });
       
      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stages',function(Blueprint $table){
            $table->dropForeign('stages_activity_id_foreign');
            $table->dropColumn('activity_id');
            $table->dropColumn('next_stage');
            $table->dropColumn('prev_stage');
            $table->dropColumn('descripcion');
            $table->dropColumn('type');
            $table->dropColumn('name');
            $table->dropUnique('stages_name_activity_id_unique');
        });
        
        
    }
}
