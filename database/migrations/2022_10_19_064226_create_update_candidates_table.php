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
        Schema::create('update_candidates', function (Blueprint $table) {
            $table->unsignedBigInteger('doc_id');
            $table->boolean('manual');
            $table->unsignedTinyInteger('justification_status');
            $table->text('justification');
            $table->unsignedTinyInteger('affected_legislation_type');
            $table->boolean('affected_available');
            $table->json('affected_legislation');
            $table->timestamp('checked_at')->nullable();
            $table->text('checked_comment');
            $table->unsignedTinyInteger('notification_status');
            $table->unsignedBigInteger('notification_status_reason_id')->nullable();
            $table->unsignedBigInteger('content_resource_id')->nullable();
            $table->unsignedInteger('task_id')->nullable();
            $table->timestamp('last_update_check')->nullable();
            $table->timestamps();

            $table->foreign('doc_id')->references('id')->on('docs')->cascadeOnDelete();

            $table->foreign('notification_status_reason_id', 'candidate_notification_status_reason_foreign')
                ->references('id')
                ->on('notification_status_reasons')
                ->nullOnDelete();

            $table->foreign('content_resource_id', 'candidate_content_resource_foreign')
                ->references('id')
                ->on('content_resources')
                ->nullOnDelete();

            $table->foreign('task_id')
                ->references('id')
                ->on('librarian_tasks')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('update_candidates');
    }
};
