<?php

use App\Enums\Arachno\CrawlType;
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
        Schema::table('url_frontier_links', function (Blueprint $table) {
            $table->unsignedTinyInteger('crawl_type')->default(CrawlType::GENERAL->value)->after('crawl_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('url_frontier_links', function (Blueprint $table) {
            $table->dropColumn('crawl_type');
        });
    }
};
