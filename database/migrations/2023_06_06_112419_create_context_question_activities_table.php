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
        Schema::create('context_question_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('context_question_id');
            $table->unsignedInteger('place_id');
            $table->unsignedTinyInteger('activity_type');
            $table->unsignedTinyInteger('from_status')->nullable();
            $table->unsignedTinyInteger('to_status')->nullable();
            $table->unsignedInteger('comment_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('context_question_id')->references('id')->on('corpus_context_questions')->cascadeOnDelete();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('comment_id')->references('id')->on('comments')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('context_question_activities');
    }
};
