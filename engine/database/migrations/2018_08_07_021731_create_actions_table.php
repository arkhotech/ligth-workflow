<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('command');
            $table->timestamps();
        });
        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedInteger('pre_activity')->before('name')->nullable();
            $table->unsignedInteger('post_activity')->before('pre_activity')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('actions');
        
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('pre_activity');
            $table->dropColumn('post_activity');
        });
        
    }
}
