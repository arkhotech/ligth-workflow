<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('activity_instance_id');
            $table->unsignedInteger('process_instance_id');
            $table->string('driver')->default('fs'); //Tipo de archivo, s3, fs, alfresco u otro
            $table->string('name')->notNull();
            $table->string('extension');
            $table->string('mime_type')->default('text/plain');
            $table->string('url');  //location
            $table->json('metadata');
            $table->string('description');
            
            $table->foreignKey('activity_instance_id')
                    ->references('id')
                    ->on('activity_instances')
                    ->onDelete('null');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
