<?php

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
        Schema::table('corpus_references', function (Blueprint $table) {
            $table->dropForeign('references_summary_id_foreign');
            $table->dropColumn(['summary_id']);
        });
        Schema::table('corpus_legal_statements', function (Blueprint $table) {
            $table->dropForeign('legal_statements_summary_id_foreign');
            $table->dropColumn(['summary_id']);
        });
        Schema::dropIfExists('corpus_summaries');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("CREATE TABLE `corpus_summaries` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `work_id` int unsigned DEFAULT NULL,
            `citation_id` bigint unsigned DEFAULT NULL,
            `element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `summary_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `update_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `update_pending` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `summaries_work_id_foreign` (`work_id`),
            CONSTRAINT `summaries_work_id_foreign` FOREIGN KEY (`work_id`) REFERENCES `corpus_works` (`id`) ON DELETE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        Schema::table('corpus_legal_statements', function (Blueprint $table) {
            $table->unsignedInteger('summary_id')
                ->nullable()
                ->after('highlights');

            $table->foreign('summary_id', 'legal_statements_summary_id_foreign')
                ->references('id')
                ->on('corpus_summaries')
                ->onDelete('set null');
        });

        Schema::table('corpus_references', function (Blueprint $table) {
            $table->unsignedInteger('summary_id')
                ->nullable()
                ->after('title');

            $table->foreign('summary_id', 'references_summary_id_foreign')
                ->references('id')
                ->on('corpus_summaries')
                ->onDelete('set null');
        });
    }
};
