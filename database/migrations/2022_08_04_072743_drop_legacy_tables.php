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
        Schema::dropIfExists('contract_place');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('corpus_assessment_item_citation');
        Schema::dropIfExists('corpus_legal_domain_ancestry');
        Schema::dropIfExists('corpus_migrations');
        Schema::dropIfExists('librarian_migrations');
        Schema::dropIfExists('librarian_oauth_auth_codes');
        Schema::dropIfExists('librarian_oauth_access_tokens');
        Schema::dropIfExists('librarian_oauth_personal_access_clients');
        Schema::dropIfExists('librarian_oauth_refresh_tokens');
        Schema::dropIfExists('librarian_oauth_clients');
        Schema::dropIfExists('magic_links');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::raw('
            CREATE TABLE `magic_links` (
            `user_id` int unsigned NOT NULL,
            `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            KEY `magic_links_user_id_index` (`user_id`),
            KEY `magic_links_token_index` (`token`),
            CONSTRAINT `magic_links_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `librarian_oauth_clients` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int DEFAULT NULL,
            `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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

        DB::raw('
            CREATE TABLE `librarian_oauth_refresh_tokens` (
            `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `revoked` tinyint(1) NOT NULL,
            `expires_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `librarian_oauth_personal_access_clients` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `client_id` int NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `oauth_personal_access_clients_client_id_index` (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `librarian_oauth_access_tokens` (
            `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `user_id` int DEFAULT NULL,
            `client_id` int NOT NULL,
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

        DB::raw('
           CREATE TABLE `librarian_oauth_auth_codes` (
            `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `user_id` int NOT NULL,
            `client_id` int NOT NULL,
            `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `revoked` tinyint(1) NOT NULL,
            `expires_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `librarian_migrations` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `batch` int NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `corpus_migrations` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `batch` int NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `corpus_legal_domain_ancestry` (
            `parent_id` int unsigned NOT NULL,
            `child_id` int unsigned NOT NULL,
            PRIMARY KEY (`parent_id`,`child_id`),
            KEY `legal_domain_ancestry_child_id_foreign` (`child_id`),
            CONSTRAINT `legal_domain_ancestry_child_id_foreign` FOREIGN KEY (`child_id`) REFERENCES `corpus_legal_domains` (`id`) ON DELETE CASCADE,
            CONSTRAINT `legal_domain_ancestry_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `corpus_legal_domains` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `corpus_assessment_item_citation` (
            `assessment_item_id` int unsigned NOT NULL,
            `citation_id` bigint unsigned NOT NULL,
            PRIMARY KEY (`assessment_item_id`,`citation_id`),
            KEY `assessment_item_citation_citation_id_foreign` (`citation_id`),
            CONSTRAINT `assessment_item_citation_assessment_item_id_foreign` FOREIGN KEY (`assessment_item_id`) REFERENCES `corpus_assessment_items` (`id`) ON DELETE CASCADE,
            CONSTRAINT `assessment_item_citation_citation_id_foreign` FOREIGN KEY (`citation_id`) REFERENCES `corpus_citations` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `contracts` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `start_date` date NOT NULL,
            `end_date` date NOT NULL,
            `invoice_frequency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `annual_value` double(8,2) NOT NULL,
            `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `partner_id` int unsigned DEFAULT NULL,
            `organisation_id` int unsigned NOT NULL,
            `partner_discount` double(8,2) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `contracts_partner_id_index` (`partner_id`),
            KEY `contracts_organisation_id_index` (`organisation_id`),
            CONSTRAINT `contracts_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
            CONSTRAINT `contracts_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        DB::raw('
            CREATE TABLE `contract_place` (
            `contract_id` int unsigned NOT NULL,
            `place_id` int unsigned NOT NULL,
            PRIMARY KEY (`contract_id`,`place_id`),
            KEY `contract_place_place_id_foreign` (`place_id`),
            CONSTRAINT `contract_place_contract_id_foreign` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
            CONSTRAINT `contract_place_place_id_foreign` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
};
