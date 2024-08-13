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
        Schema::create('notification_handover_update_candidate_legislation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_handover_id');
            $table->unsignedBigInteger('update_candidate_legislation_id');
            $table->unsignedInteger('task_id')->nullable();
            $table->unsignedTinyInteger('status');
            $table->json('meta');
            $table->timestamps();

            $table->foreign('notification_handover_id', 'handover_legislation_foreign')->references('id')->on('notification_handovers')->cascadeOnDelete();
            $table->foreign('update_candidate_legislation_id', 'candidate_legislation_foreign')->references('id')->on('update_candidate_legislations')->cascadeOnDelete();
            $table->foreign('task_id', 'handover_legislation_task_id')->references('id')->on('librarian_tasks')->nullOnDelete();

            $table->unique(['notification_handover_id', 'update_candidate_legislation_id'], 'notification_handover_update_candidate_legislation_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_handover_update_candidate_legislation');
    }
};
