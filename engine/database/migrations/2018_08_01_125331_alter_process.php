<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table("processes",function(Blueprint $table){
            $table->unsignedInteger('role_owner_id');
            $table->unique(['name','domain_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("processes",function(Blueprint $table){
            $table->dropColumn('role_owner_id');
            $table->dropUnique('processes_name_domain_id_unique');
        });
    }
}
