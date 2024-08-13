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
        Schema::create('librarian_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('context_due_date')->nullable();
            $table->date('actions_due_date')->nullable();
            $table->unsignedInteger('board_id')->nullable();
            $table->unsignedBigInteger('production_pod_id')->nullable();
            $table->unsignedInteger('organisation_id')->nullable();
            $table->unsignedInteger('owner_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->string('language_code', 10)->nullable();
            $table->unsignedTinyInteger('rag_status')->default(0);
            $table->unsignedTinyInteger('project_size')->default(1);
            $table->unsignedTinyInteger('project_status')->default(0);
            $table->unsignedInteger('author_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('board_id')->references('id')->on('librarian_boards')->onDelete('set null');
            $table->foreign('production_pod_id')->references('id')->on('production_pods')->onDelete('set null');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('set null');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('corpus_locations')->onDelete('set null');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('librarian_projects');
    }
};
