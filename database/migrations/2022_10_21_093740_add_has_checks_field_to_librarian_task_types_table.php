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
        Schema::table('librarian_task_types', function (Blueprint $table) {
            $table->boolean('has_checks')->default(false)->after('assignee_can_close');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('librarian_task_types', function (Blueprint $table) {
            $table->dropColumn(['has_checks']);
        });
    }
};
