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
        Schema::table('update_candidates', function (Blueprint $table) {
            $table->unsignedTinyInteger('check_sources_status')->nullable()->after('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('update_candidates', function (Blueprint $table) {
            $table->dropColumn([
                'check_sources_status',
            ]);
        });
    }
};
