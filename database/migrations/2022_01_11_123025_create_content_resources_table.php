<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_resources', function (Blueprint $table) {
            $table->id();
            $table->string('content_hash', 40)->nullable();
            $table->string('file_name', 100)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('path', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
        });

        DB::statement('ALTER TABLE `content_resources` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT content_resources_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_resources');
    }
}
