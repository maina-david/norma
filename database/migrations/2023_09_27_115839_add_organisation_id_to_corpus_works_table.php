<?php

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
        Schema::table('corpus_works', function (Blueprint $table) {
            $table->unsignedInteger('organisation_id')->nullable()->after('content_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_works', function (Blueprint $table) {
            $table->dropColumn(['organisation_id']);
        });
    }
};
