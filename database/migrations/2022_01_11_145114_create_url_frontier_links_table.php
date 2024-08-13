<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlFrontierLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('url_frontier_links', function (Blueprint $table) {
            $table->id();
            $table->string('url', 511);
            $table->unsignedBigInteger('crawl_id')->nullable();
            $table->unsignedBigInteger('doc_id')->nullable();
            $table->unsignedBigInteger('parent_toc_item_id')->nullable();
            $table->mediumText('anchor_text')->nullable();
            $table->string('referer', 511)->nullable();
            $table->boolean('needs_browser')->default(false);
            $table->string('wait_for', 511)->nullable();
            $table->float('priority')->nullable();
            $table->timestamp('crawled_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();

            $table->foreign('crawl_id')
                ->references('id')
                ->on('crawls')
                ->onDelete('set null');

            $table->foreign('doc_id')
                ->references('id')
                ->on('docs')
                ->onDelete('set null');

            $table->foreign('parent_toc_item_id')
                ->references('id')
                ->on('toc_items')
                ->onDelete('set null');
        });

        DB::statement('ALTER TABLE `url_frontier_links` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT url_frontier_links_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('url_frontier_links', function (Blueprint $table) {
            $table->dropForeign(['crawl_id']);
            $table->dropForeign(['doc_id']);
            $table->dropForeign(['parent_toc_item_id']);
        });
        Schema::dropIfExists('url_frontier_links');
    }
}
