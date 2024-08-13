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
        Schema::table('corpus_work_expressions', function (Blueprint $table) {
            $table->dropColumn(['path', 'stylesheet', 'source_path', 'toc', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_work_expressions', function (Blueprint $table) {
            $table->string('path')->nullable()->after('language_code');
            $table->string('stylesheet')->nullable()->after('path');
            $table->string('source_path')->nullable()->after('stylesheet');
            $table->longText('toc')->nullable()->after('source_path');
            $table->longText('content')->nullable()->after('toc');
        });
    }
};
