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
        Schema::create('update_candidate_legislations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doc_id')->nullable();
            $table->unsignedTinyInteger('type');
            $table->string('title', 1000)->nullable();
            $table->string('title_translation', 1000)->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->unsignedInteger('work_id')->nullable();
            $table->unsignedBigInteger('catalogue_doc_id')->nullable();
            $table->longText('handover_comments')->nullable();
            $table->timestamps();

            $table->foreign('doc_id')->references('id')->on('docs')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('corpus_works')->nullOnDelete();
            $table->foreign('catalogue_doc_id')->references('id')->on('catalogue_docs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('update_candidate_legislations');
    }
};
