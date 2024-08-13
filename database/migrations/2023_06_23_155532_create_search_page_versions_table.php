<?php

use App\Models\Arachno\Crawl;
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
        Schema::create('search_page_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_page_id')->nullable();
            $table->unsignedBigInteger('crawl_id')->nullable();
            $table->string('content_hash', 40)->nullable();
            $table->string('content_path', 100)->nullable();
            $table->timestamps();

            $table->index('search_page_id', 'search_page_versions_search_page_id_foreign');
            $table->foreign('search_page_id')->references('id')->on('search_pages')->onDelete('cascade');
            $table->foreign('crawl_id')->references('id')->on((new Crawl())->getTable())->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_page_versions', function (Blueprint $table) {
            $table->dropForeign(['search_page_id']);
            $table->dropForeign(['crawl_id']);
        });
        Schema::dropIfExists('search_page_versions');
    }
};
