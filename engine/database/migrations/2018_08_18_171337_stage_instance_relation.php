<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StageInstanceRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
       
        Schema::table('stage_instances',function(Blueprint $table){
            //$table->dropForeign('stage_instances_activity_instance_id_foreign');
            $table->unsignedInteger('stage_id');
            $table->foreign('stage_id')
                    ->references('id')
                    ->on('stages')
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
        Schema::table('stage_instances',function(Blueprint $table){
            $table->dropForeign('stage_instances_stage_id_foreign');
            $table->dropColumn('stage_id');
        });
    }
}
