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
        Schema::create('location_source', function (Blueprint $table) {
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('source_id');

            $table->foreign('location_id', 'location_source_location_foreign')->references('id')->on('corpus_locations')->cascadeOnDelete();
            $table->foreign('source_id', 'location_source_source_foreign')->references('id')->on('corpus_sources')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_source');
    }
};
