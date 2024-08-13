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
        Schema::table('corpus_legal_updates', function (Blueprint $table) {
            $table->longText('automated_highlights')->after('highlights')->nullable();
            $table->longText('automated_update_report')->after('update_report')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_legal_updates', function (Blueprint $table) {
            $table->dropColumn(['highlights', 'update_report']);
        });
    }
};
