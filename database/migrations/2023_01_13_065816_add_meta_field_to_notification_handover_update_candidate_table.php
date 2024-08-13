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
        Schema::table('notification_handover_update_candidate', function (Blueprint $table) {
            $table->json('meta')->nullable();
        });

        DB::table('notification_handover_update_candidate')->update(['meta' => []]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_handover_update_candidate', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
