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
        Schema::create('applicability_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('activity_type');
            $table->unsignedTinyInteger('previous')->nullable();
            $table->unsignedTinyInteger('current');
            $table->unsignedInteger('place_id');
            $table->unsignedBigInteger('applicability_note_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('context_question_id')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->foreign('applicability_note_id')->references('id')->on('applicability_notes')->nullOnDelete();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('context_question_id')->references('id')->on('corpus_context_questions')->nullOnDelete();
            $table->foreign('reference_id')->references('id')->on('corpus_references')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicability_activities');
    }
};
