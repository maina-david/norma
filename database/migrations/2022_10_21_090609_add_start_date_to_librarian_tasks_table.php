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
        Schema::table('librarian_tasks', function (Blueprint $table) {
            $table->timestamp('start_date')->nullable()->after('work_expression_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('librarian_tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date']);
        });
    }
};
