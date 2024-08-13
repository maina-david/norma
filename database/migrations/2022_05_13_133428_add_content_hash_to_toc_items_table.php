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
        Schema::table('toc_items', function (Blueprint $table) {
            $table->string('content_hash', 40)->nullable()->after('source_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('toc_items', function (Blueprint $table) {
            $table->dropColumn(['content_hash']);
        });
    }
};
