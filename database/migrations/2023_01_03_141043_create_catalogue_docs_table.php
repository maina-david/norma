<?php

use App\Models\Arachno\Crawler;
use App\Models\Arachno\Source;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogue_docs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 3000)->nullable();
            $table->string('source_unique_id')->nullable();
            $table->string('start_url', 3000)->nullable();
            $table->string('view_url', 3000)->nullable();
            $table->string('http_method', 7)->nullable();
            $table->json('post_data')->nullable();
            $table->string('post_data_type', 10)->nullable();
            $table->unsignedInteger('source_id')->nullable();
            $table->unsignedBigInteger('crawler_id')->nullable();
            $table->timestamp('fetch_started_at')->nullable();
            $table->timestamps();

            $table->foreign('source_id')
                ->references('id')
                ->on((new Source())->getTable())
                ->onDelete('set null');

            $table->foreign('crawler_id')
                ->references('id')
                ->on((new Crawler())->getTable())
                ->onDelete('set null');
        });

        DB::statement('ALTER TABLE `catalogue_docs` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT catalogue_docs_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('catalogue_docs', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropForeign(['crawler_id']);
        });
        Schema::dropIfExists('catalogue_docs');
    }
};
