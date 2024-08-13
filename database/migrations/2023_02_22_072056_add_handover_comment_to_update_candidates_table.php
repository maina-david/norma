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
            $table->text('handover_comment')->nullable()->after('last_update_check');
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
            $table->dropColumn(['handover_comment']);
        });
    }
};
