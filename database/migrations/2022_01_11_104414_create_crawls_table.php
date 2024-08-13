<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrawlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crawler_id')->nullable();
            $table->json('cookies')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            $table->foreign('crawler_id')
                ->references('id')
                ->on('crawlers')
                ->onDelete('cascade');
        });

        DB::statement('ALTER TABLE `crawls` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT crawls_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawls');
    }
}
