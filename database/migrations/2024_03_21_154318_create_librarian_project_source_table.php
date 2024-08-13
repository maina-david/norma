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
        Schema::create('librarian_project_source', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('source_id');

            $table->foreign('project_id')->references('id')->on('librarian_projects')->onDelete('cascade');
            $table->foreign('source_id')->references('id')->on('corpus_sources')->onDelete('cascade');

            $table->primary(['project_id', 'source_id'], 'librarian_project_source_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('librarian_project_source');
    }
};
