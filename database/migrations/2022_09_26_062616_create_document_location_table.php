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
        Schema::create('librarian_document_location', function (Blueprint $table) {
            $table->unsignedInteger('document_id');
            $table->unsignedInteger('location_id');

            $table->foreign('document_id')->references('id')->on('librarian_documents')->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on('corpus_locations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('librarian_document_location');
    }
};
