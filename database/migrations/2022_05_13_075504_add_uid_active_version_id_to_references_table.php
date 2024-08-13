<?php

use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
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
        Schema::table((new Reference())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('active_text_version_id')->nullable()->after('cached_content');
            $table->unsignedBigInteger('toc_item_id')->nullable()->after('active_text_version_id');

            $table->foreign('active_text_version_id')
                ->references('id')
                ->on('corpus_reference_text_versions')
                ->onDelete('set null');

            $table->foreign('toc_item_id')
                ->references('id')
                ->on((new TocItem())->getTable())
                ->onDelete('set null');

            DB::statement('ALTER TABLE `' . (new Reference())->getTable() . '` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT corpus_references_uid_unique UNIQUE (`uid`)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new Reference())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['toc_item_id']);
            $table->dropForeign(['active_text_version_id']);
            $table->dropUnique(['uid']);

            $table->dropColumn(['toc_item_id']);
            $table->dropColumn(['active_text_version_id']);
            $table->dropColumn(['uid']);
        });
    }
};
