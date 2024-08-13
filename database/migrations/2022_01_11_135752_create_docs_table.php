<?php

use App\Models\Arachno\Source;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->string('source_unique_id', 255)->nullable();
            $table->unsignedBigInteger('crawl_id')->nullable();
            $table->unsignedInteger('source_id')->nullable();
            $table->unsignedBigInteger('crawler_id')->nullable();
            $table->unsignedInteger('work_id')->nullable();
            $table->string('title', 1000);
            $table->string('title_translation', 1000)->nullable();
            $table->string('language_code', 5)->nullable();
            $table->string('start_url', 511)->nullable();
            $table->unsignedBigInteger('work_type_id')->nullable();
            $table->unsignedInteger('primary_location_id')->nullable();
            $table->string('work_number')->nullable();
            $table->string('publication_number')->nullable();
            $table->string('publication_document_number')->nullable();
            $table->date('work_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('version_hash', 40)->nullable();
            $table->timestamp('update_detected_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            $table->foreign('crawl_id')
                ->references('id')
                ->on('crawls')
                ->onDelete('set null');

            $table->foreign('work_type_id')
                ->references('id')
                ->on('work_types')
                ->onDelete('set null');

            $table->foreign('source_id')
                ->references('id')
                ->on((new Source())->getTable())
                ->onDelete('set null');

            $table->foreign('crawler_id')
                ->references('id')
                ->on('crawlers')
                ->onDelete('set null');

            $table->foreign('primary_location_id')
                ->references('id')
                ->on((new Location())->getTable())
                ->onDelete('set null');

            $table->foreign('work_id')
                ->references('id')
                ->on((new Work())->getTable())
                ->onDelete('set null');
        });

        DB::statement('ALTER TABLE `docs` ADD `uid` BINARY(20) AFTER `id`, ADD INDEX (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('docs', function (Blueprint $table) {
            $table->dropForeign(['primary_location_id']);
            $table->dropForeign(['crawl_id']);
            $table->dropForeign(['work_type_id']);
            $table->dropForeign(['source_id']);
            $table->dropForeign(['crawler_id']);
            $table->dropForeign(['work_id']);
        });
        Schema::dropIfExists('docs');
    }
}
