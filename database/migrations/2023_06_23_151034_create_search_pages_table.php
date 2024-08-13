<?php

use App\Models\Arachno\Source;
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
        Schema::create('search_pages', function (Blueprint $table) {
            $table->id();
            $table->string('url', 3000)->nullable();
            $table->string('latest_content_hash', 40)->nullable();
            $table->unsignedInteger('source_id')->nullable();
            $table->timestamp('last_crawled_at')->nullable()->useCurrent();
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on((new Source())->getTable())->onDelete('set null');
        });

        DB::statement('ALTER TABLE `search_pages` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT search_pages_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_pages', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
        });
        Schema::dropIfExists('search_pages');
    }
};
