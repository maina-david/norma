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
        Schema::table('notification_handovers', function (Blueprint $table) {
            $table->json('include_meta')->nullable()->after('text');
        });

        DB::table('notification_handovers')->update(['include_meta' => []]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_handovers', function (Blueprint $table) {
            $table->dropColumn('include_meta');
        });
    }
};
