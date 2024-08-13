<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullTextIndexesToTagsWorksFilesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corpus_tags', function (Blueprint $table) {
            $table->fulltext('title');
        });

        Schema::table('corpus_works', function (Blueprint $table) {
            $table->fulltext('title');
            $table->fulltext('title_translation');
        });
        Schema::table('files', function (Blueprint $table) {
            $table->fulltext('title');
        });
        Schema::table('user_tags', function (Blueprint $table) {
            $table->fulltext('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_tags', function (Blueprint $table) {
            $table->dropFulltext('user_tags_title_fulltext');
        });
        Schema::table('files', function (Blueprint $table) {
            $table->dropFulltext('files_title_fulltext');
        });
        Schema::table('corpus_works', function (Blueprint $table) {
            $table->dropFulltext('corpus_works_title_translation_fulltext');
            $table->dropFulltext('corpus_works_title_fulltext');
        });
        Schema::table('corpus_tags', function (Blueprint $table) {
            $table->dropFulltext('corpus_tags_title_fulltext');
        });
    }
}
