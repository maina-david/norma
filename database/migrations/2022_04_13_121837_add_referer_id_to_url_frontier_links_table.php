<?php

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table((new UrlFrontierLink())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('referer_id')->nullable()->after('parent_toc_item_id');
            $table->unsignedBigInteger('content_cache_id')->nullable()->after('crawl_id');

            $table->foreign('referer_id')
                ->references('id')
                ->on((new UrlFrontierLink())->getTable())
                ->onDelete('set null');

            $table->foreign('content_cache_id')
                ->references('id')
                ->on((new ContentCache())->getTable())
                ->onDelete('set null');

            $table->index('crawled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new UrlFrontierLink())->getTable(), function (Blueprint $table) {
            $table->dropIndex(['crawled_at']);
            $table->dropForeign(['referer_id']);
            $table->dropForeign(['content_cache_id']);
            $table->dropColumn(['referer_id']);
            $table->dropColumn(['content_cache_id']);
        });
    }
};
