<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('admin_email');
            $table->string('company_name');
            
            $table->timestamps();
        });
        Schema::table('processes',function(Blueprint $table){
            $table->unsignedInteger('domain_id');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processes',function(Blueprint $table){
            
            $table->dropForeign('processes_domain_id_foreign');
            $table->dropColumn('domain_id');
        });
        Schema::dropIfExists('domains');
    }
}
