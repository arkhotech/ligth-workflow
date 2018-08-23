<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Test extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('process_variables',function(Blueprint $table){
            //$table->dropForeign('process_variables_id_process_foreign');
            //$table->dropColumn('id_process');
            $table->unsignedInteger('process_id');
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            
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
    }
}
