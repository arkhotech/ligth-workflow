<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FieldsProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('fields',function(Blueprint $table){
            $table->boolean('readOnly');
            $table->boolean('required');
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
         Schema::table('fields',function(Blueprint $table){
            $table->removeColumn('readOnly');
            $table->removeColumn('required');
        });
    }
}
