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
        Schema::table('corpus_sources', function (Blueprint $table) {
            $table->boolean('hide_content')->default(false)->after('permission_obtained');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_sources', function (Blueprint $table) {
            $table->dropColumn(['hide_content']);
        });
    }
};
