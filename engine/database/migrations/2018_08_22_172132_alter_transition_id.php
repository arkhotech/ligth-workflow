<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTransitionId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('transitions',function(Blueprint $table){
//            Schema::ifExistsForeign('transitions_prev_activity_id_foreign',function(){
//                )
//           $table->dropForeign('transitions_prev_activity_id_foreign');
//            $table->dropUnique('transitions_prev_activity_id_next_activity_id_unique');
            $table->unique(['prev_activity_id','next_activity_id']);
            $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
        });
    }

    /** d
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
         //
        Schema::table('transitions',function(Blueprint $table){
//            $table->dropForeign('transitions_next_activity_id_foreign');
//            $table->dropForeign('transitions_prev_activity_id_foreign');
            $table->dropUnique('transitions_prev_activity_id_next_activity_id_unique');
            $table->dropForeign('transitions_process_id_foreign');
        });

    }
}
