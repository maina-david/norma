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
        Schema::create('legal_domain_librarian_project', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('legal_domain_id');

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('librarian_projects')->onDelete('cascade');
            $table->foreign('legal_domain_id')->references('id')->on('corpus_legal_domains')->onDelete('cascade');

            $table->primary(['project_id', 'legal_domain_id'], 'legal_domain_librarian_project_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('legal_domain_librarian_project');
    }
};
