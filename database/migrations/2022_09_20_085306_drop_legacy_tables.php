<?php

use Illuminate\Database\Migrations\Migration;
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
        Schema::dropIfExists('assessment_item_file');
        Schema::dropIfExists('assessment_item_place');
        Schema::dropIfExists('corpus_citation_context_question');
        Schema::dropIfExists('corpus_citation_legal_domain');
        Schema::dropIfExists('corpus_citation_location');
        Schema::dropIfExists('corpus_citation_tag');
        Schema::dropIfExists('corpus_citation_work_expression');
        Schema::dropIfExists('corpus_expression_citation_updates');
        Schema::dropIfExists('corpus_failed_jobs');
        Schema::dropIfExists('librarian_failed_jobs');
        Schema::dropIfExists('corpus_job_batches');
        Schema::dropIfExists('corpus_legal_domain_legal_statement');
        Schema::dropIfExists('corpus_legal_domain_work');
        Schema::dropIfExists('corpus_legal_statement_consequence');
        Schema::dropIfExists('corpus_legal_statement_legal_statement');
        Schema::dropIfExists('corpus_location_work');
        Schema::dropIfExists('corpus_oauth_access_tokens');
        Schema::dropIfExists('corpus_oauth_auth_codes');
        Schema::dropIfExists('corpus_oauth_clients');
        Schema::dropIfExists('corpus_oauth_personal_access_clients');
        Schema::dropIfExists('corpus_oauth_refresh_tokens');
        Schema::dropIfExists('corpus_tag_work');
        Schema::dropIfExists('corpus_users');
        Schema::dropIfExists('corpus_context_question_work');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('
            CREATE TABLE `corpus_context_question_work` (
            `context_question_id` int unsigned NOT NULL,
            `work_id` int unsigned NOT NULL,
            `element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`context_question_id`,`work_id`,`element_id`),
            KEY `context_question_work_work_id_foreign` (`work_id`),
            CONSTRAINT `context_question_work_context_question_id_foreign` FOREIGN KEY (`context_question_id`) REFERENCES `corpus_context_questions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `context_question_work_work_id_foreign` FOREIGN KEY (`work_id`) REFERENCES `corpus_works` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement("
            CREATE TABLE `corpus_users` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `fname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `sname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `email_verified_at` timestamp NULL DEFAULT NULL,
            `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Africa/Johannesburg',
            `language` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
            `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `users_email_unique` (`email`)
            ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        DB::statement('
            CREATE TABLE `corpus_tag_work` (
            `tag_id` int unsigned NOT NULL,
            `work_id` int unsigned NOT NULL,
            `element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`tag_id`,`work_id`,`element_id`),
            KEY `tag_work_work_id_foreign` (`work_id`),
            CONSTRAINT `tag_work_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `corpus_tags` (`id`) ON DELETE CASCADE,
            CONSTRAINT `tag_work_work_id_foreign` FOREIGN KEY (`work_id`) REFERENCES `corpus_works` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_oauth_refresh_tokens` (
            `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `revoked` tinyint(1) NOT NULL,
            `expires_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_oauth_personal_access_clients` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `client_id` int unsigned NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `oauth_personal_access_clients_client_id_index` (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_oauth_clients` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int DEFAULT NULL,
            `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `personal_access_client` tinyint(1) NOT NULL,
            `password_client` tinyint(1) NOT NULL,
            `revoked` tinyint(1) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `oauth_clients_user_id_index` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_oauth_auth_codes` (
            `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `user_id` int NOT NULL,
            `client_id` int unsigned NOT NULL,
            `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `revoked` tinyint(1) NOT NULL,
            `expires_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_oauth_access_tokens` (
            `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `user_id` int DEFAULT NULL,
            `client_id` int unsigned NOT NULL,
            `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `revoked` tinyint(1) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `expires_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `oauth_access_tokens_user_id_index` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_location_work` (
            `work_id` int unsigned NOT NULL,
            `location_id` int unsigned NOT NULL,
            `element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`work_id`,`location_id`,`element_id`),
            KEY `location_work_location_id_foreign` (`location_id`),
            CONSTRAINT `location_work_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `corpus_locations` (`id`) ON DELETE CASCADE,
            CONSTRAINT `location_work_work_id_foreign` FOREIGN KEY (`work_id`) REFERENCES `corpus_works` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_legal_statement_legal_statement` (
            `parent_id` int unsigned NOT NULL,
            `child_id` int unsigned NOT NULL,
            `parent_work_id` int unsigned NOT NULL,
            `child_work_id` int unsigned NOT NULL,
            `link_type` tinyint unsigned NOT NULL,
            PRIMARY KEY (`parent_id`,`child_id`,`link_type`),
            KEY `legal_statement_legal_statement_child_id_foreign` (`child_id`),
            KEY `parent_work_foreign` (`parent_work_id`),
            KEY `child_work_foreign` (`child_work_id`),
            CONSTRAINT `child_work_foreign` FOREIGN KEY (`child_work_id`) REFERENCES `corpus_works` (`id`) ON DELETE CASCADE,
            CONSTRAINT `legal_statement_legal_statement_child_id_foreign` FOREIGN KEY (`child_id`) REFERENCES `corpus_legal_statements` (`id`) ON DELETE CASCADE,
            CONSTRAINT `legal_statement_legal_statement_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `corpus_legal_statements` (`id`) ON DELETE CASCADE,
            CONSTRAINT `parent_work_foreign` FOREIGN KEY (`parent_work_id`) REFERENCES `corpus_works` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_legal_statement_consequence` (
            `legal_statement_id` int unsigned NOT NULL,
            `consequence_id` int unsigned NOT NULL,
            PRIMARY KEY (`legal_statement_id`,`consequence_id`),
            KEY `legal_statement_consequence_consequence_id_foreign` (`consequence_id`),
            CONSTRAINT `legal_statement_consequence_consequence_id_foreign` FOREIGN KEY (`consequence_id`) REFERENCES `corpus_consequences` (`id`) ON DELETE CASCADE,
            CONSTRAINT `legal_statement_consequence_legal_statement_id_foreign` FOREIGN KEY (`legal_statement_id`) REFERENCES `corpus_legal_statements` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_legal_domain_work` (
            `legal_domain_id` int unsigned NOT NULL,
            `work_id` int unsigned NOT NULL,
            `element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`legal_domain_id`,`work_id`,`element_id`),
            KEY `legal_domain_work_work_id_foreign` (`work_id`),
            CONSTRAINT `legal_domain_work_legal_domain_id_foreign` FOREIGN KEY (`legal_domain_id`) REFERENCES `corpus_legal_domains` (`id`) ON DELETE CASCADE,
            CONSTRAINT `legal_domain_work_work_id_foreign` FOREIGN KEY (`work_id`) REFERENCES `corpus_works` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_legal_domain_legal_statement` (
            `legal_domain_id` int unsigned NOT NULL,
            `legal_statement_id` int unsigned NOT NULL,
            PRIMARY KEY (`legal_domain_id`,`legal_statement_id`),
            KEY `legal_domain_legal_statement_legal_statement_id_foreign` (`legal_statement_id`),
            CONSTRAINT `legal_domain_legal_statement_legal_domain_id_foreign` FOREIGN KEY (`legal_domain_id`) REFERENCES `corpus_legal_domains` (`id`) ON DELETE CASCADE,
            CONSTRAINT `legal_domain_legal_statement_legal_statement_id_foreign` FOREIGN KEY (`legal_statement_id`) REFERENCES `corpus_legal_statements` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_job_batches` (
            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `total_jobs` int NOT NULL,
            `pending_jobs` int NOT NULL,
            `failed_jobs` int NOT NULL,
            `failed_job_ids` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `options` mediumtext COLLATE utf8mb4_unicode_ci,
            `cancelled_at` int DEFAULT NULL,
            `created_at` int NOT NULL,
            `finished_at` int DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `librarian_failed_jobs` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_failed_jobs` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement("
            CREATE TABLE `corpus_expression_citation_updates` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `work_expression_id` int unsigned NOT NULL,
            `position` int unsigned NOT NULL,
            `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `level` int unsigned NOT NULL DEFAULT '1',
            `is_toc_item` tinyint(1) NOT NULL DEFAULT '1',
            `visible` tinyint(1) NOT NULL DEFAULT '1',
            `is_section` tinyint(1) NOT NULL DEFAULT '0',
            `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `applied` tinyint(1) NOT NULL DEFAULT '0',
            `element_id` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `expression_citation_updates_work_expression_id_foreign` (`work_expression_id`),
            CONSTRAINT `expression_citation_updates_work_expression_id_foreign` FOREIGN KEY (`work_expression_id`) REFERENCES `corpus_work_expressions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        DB::statement("
            CREATE TABLE `corpus_citation_work_expression` (
            `citation_id` bigint unsigned NOT NULL,
            `work_expression_id` int unsigned NOT NULL,
            `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `status` tinyint unsigned NOT NULL DEFAULT '1',
            PRIMARY KEY (`citation_id`,`work_expression_id`),
            KEY `citation_work_expression_work_expression_id_foreign` (`work_expression_id`),
            CONSTRAINT `citation_work_expression_citation_id_foreign` FOREIGN KEY (`citation_id`) REFERENCES `corpus_citations` (`id`) ON DELETE CASCADE,
            CONSTRAINT `citation_work_expression_work_expression_id_foreign` FOREIGN KEY (`work_expression_id`) REFERENCES `corpus_work_expressions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        DB::statement('
            CREATE TABLE `corpus_citation_tag` (
            `citation_id` bigint unsigned NOT NULL,
            `tag_id` int unsigned NOT NULL,
            PRIMARY KEY (`citation_id`,`tag_id`),
            KEY `citation_tag_tag_id_foreign` (`tag_id`),
            CONSTRAINT `citation_tag_citation_id_foreign` FOREIGN KEY (`citation_id`) REFERENCES `corpus_citations` (`id`) ON DELETE CASCADE,
            CONSTRAINT `citation_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `corpus_tags` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_citation_location` (
            `citation_id` bigint unsigned NOT NULL,
            `location_id` int unsigned NOT NULL,
            PRIMARY KEY (`citation_id`,`location_id`),
            KEY `citation_location_location_id_foreign` (`location_id`),
            CONSTRAINT `citation_location_citation_id_foreign` FOREIGN KEY (`citation_id`) REFERENCES `corpus_citations` (`id`) ON DELETE CASCADE,
            CONSTRAINT `citation_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `corpus_locations` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_citation_legal_domain` (
            `citation_id` bigint unsigned NOT NULL,
            `legal_domain_id` int unsigned NOT NULL,
            PRIMARY KEY (`citation_id`,`legal_domain_id`),
            KEY `citation_legal_domain_legal_domain_id_foreign` (`legal_domain_id`),
            CONSTRAINT `citation_legal_domain_citation_id_foreign` FOREIGN KEY (`citation_id`) REFERENCES `corpus_citations` (`id`) ON DELETE CASCADE,
            CONSTRAINT `citation_legal_domain_legal_domain_id_foreign` FOREIGN KEY (`legal_domain_id`) REFERENCES `corpus_legal_domains` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `corpus_citation_context_question` (
            `citation_id` bigint unsigned NOT NULL,
            `context_question_id` int unsigned NOT NULL,
            PRIMARY KEY (`citation_id`,`context_question_id`),
            KEY `citation_context_question_context_question_id_foreign` (`context_question_id`),
            CONSTRAINT `citation_context_question_citation_id_foreign` FOREIGN KEY (`citation_id`) REFERENCES `corpus_citations` (`id`) ON DELETE CASCADE,
            CONSTRAINT `citation_context_question_context_question_id_foreign` FOREIGN KEY (`context_question_id`) REFERENCES `corpus_context_questions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `assessment_item_place` (
            `assessment_item_id` int unsigned NOT NULL,
            `place_id` int unsigned NOT NULL,
            `current_status` tinyint unsigned DEFAULT NULL,
            PRIMARY KEY (`assessment_item_id`,`place_id`),
            KEY `assessment_item_place_place_id_foreign` (`place_id`),
            CONSTRAINT `assessment_item_place_place_id_foreign` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        DB::statement('
            CREATE TABLE `assessment_item_file` (
            `assessment_item_id` int unsigned NOT NULL,
            `file_id` int unsigned NOT NULL,
            PRIMARY KEY (`assessment_item_id`,`file_id`),
            KEY `assessment_item_file_file_id_foreign` (`file_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
};
