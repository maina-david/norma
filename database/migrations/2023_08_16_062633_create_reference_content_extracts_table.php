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
        Schema::create('reference_content_extracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reference_id');
            $table->text('content');
            $table->timestamps();

            $table->foreign('reference_id')->references('id')->on('corpus_references')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reference_content_extracts');
    }
};
