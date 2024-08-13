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
        Schema::table('librarian_profiles', function (Blueprint $table) {
            $table->timestamp('residency_confirmed_at')->nullable()->after('is_team_admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('librarian_profiles', function (Blueprint $table) {
            $table->dropColumn(['residency_confirmed_at']);
        });
    }
};
