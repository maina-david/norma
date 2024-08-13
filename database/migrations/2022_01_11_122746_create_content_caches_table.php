<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentCachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_caches', function (Blueprint $table) {
            $table->id();
            $table->string('url', 511)->nullable();
            $table->unsignedBigInteger('crawl_id')->nullable();
            $table->json('response_headers')->nullable();
            $table->longText('processed_content')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            $table->foreign('crawl_id')
                ->references('id')
                ->on('crawls')
                ->onDelete('set null');
        });

        DB::statement('ALTER TABLE `content_caches` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT content_caches_uid_unique UNIQUE (`uid`)');
        DB::statement('ALTER TABLE `content_caches` ADD response_body LONGBLOB AFTER `response_headers`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_caches');
    }
}
