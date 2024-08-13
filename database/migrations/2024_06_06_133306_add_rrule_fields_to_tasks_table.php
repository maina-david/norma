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
        Schema::table('tasks', function (Blueprint $table) {
            $table->longText('rrule')->nullable()->after('frequency_interval');
            $table->integer('recurrence_count')->default(0)->after('rrule');
            $table->date('recurrence_start_date')->nullable()->after('recurrence_count');
            $table->unsignedBigInteger('series_task_id')->nullable()->after('recurrence_start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('rrule');
            $table->dropColumn('recurrence_count');
            $table->dropColumn('recurrence_start_date');
            $table->dropColumn('series_task_id');
        });
    }
};
