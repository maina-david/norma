<?php

use App\Enums\Tasks\TaskRepeatInterval;
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
            $table->unsignedInteger('previous_task_id')->nullable()->after('taskable_id');
            $table->unsignedInteger('frequency')->nullable()->after('due_on');
            $table->unsignedTinyInteger('frequency_interval')->default(TaskRepeatInterval::MONTH->value)->after('frequency');

            $table->foreign('previous_task_id')->references('id')->on('tasks')->nullOnDelete();
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
            $table->dropConstrainedForeignId('previous_task_id');
            $table->dropColumn(['frequency', 'frequency_interval']);
        });
    }
};
