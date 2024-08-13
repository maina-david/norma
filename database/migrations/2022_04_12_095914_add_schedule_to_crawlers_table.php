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
        Schema::table('crawlers', function (Blueprint $table) {
            $table->string('schedule')->nullable()->after('needs_browser');
            $table->boolean('enabled')->default(false)->after('schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crawlers', function (Blueprint $table) {
            $table->dropColumn(['enabled']);
            $table->dropColumn(['schedule']);
        });
    }
};
