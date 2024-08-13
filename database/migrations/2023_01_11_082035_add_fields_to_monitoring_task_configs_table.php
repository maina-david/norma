<?php

use App\Models\Workflows\MonitoringTaskConfig;
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
        MonitoringTaskConfig::truncate();

        Schema::table('monitoring_task_configs', function (Blueprint $table) {
            $table->unsignedTinyInteger('start_day')->after('last_run');
            $table->unsignedTinyInteger('frequency')->after('start_day');
            $table->unsignedTinyInteger('frequency_quantity')->after('frequency');
            $table->dropColumn(['frequency_in_days']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('monitoring_task_configs', function (Blueprint $table) {
            $table->unsignedInteger('frequency_in_days')->default(1);
            $table->dropColumn(['start_day', 'frequency', 'frequency_quantity']);
        });
    }
};
