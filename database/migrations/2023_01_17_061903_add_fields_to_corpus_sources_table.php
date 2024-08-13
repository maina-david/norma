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
            $table->string('rights')->nullable()->after('permission_note');
            $table->boolean('has_script')->default(false)->after('ingestion');
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
            $table->dropColumn(['rights', 'has_script']);
        });
    }
};
