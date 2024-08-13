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
        Schema::create('monitoring_task_configs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedInteger('source_id')->nullable();
            $table->unsignedInteger('board_id')->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('frequency_in_days')->default(1);
            $table->unsignedTinyInteger('priority');
            $table->json('locations')->nullable();
            $table->json('legal_domains')->nullable();
            $table->json('tasks');
            $table->timestamp('last_run')->nullable();
            $table->boolean('enabled');
            $table->timestamps();

            $table->foreign('board_id')->references('id')->on('librarian_boards')->nullOnDelete();
            $table->foreign('group_id')->references('id')->on('librarian_groups')->nullOnDelete();
            $table->foreign('source_id')->references('id')->on('corpus_sources')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitoring_task_configs');
    }
};
