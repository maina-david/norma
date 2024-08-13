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
        Schema::table('librarian_task_routes', function (Blueprint $table) {
            $table->string('permission')->nullable()->after('parameters');
            $table->json('permission_parameters')->nullable()->after('permission');
            $table->string('fallback')->nullable()->after('permission_parameters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('librarian_task_routes', function (Blueprint $table) {
            $table->dropColumn(['permission', 'permission_parameters', 'fallback']);
        });
    }
};
