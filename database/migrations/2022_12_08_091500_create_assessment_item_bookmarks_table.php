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
        Schema::create('assessment_item_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('assessment_item_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('assessment_item_id')->references('id')->on('corpus_assessment_items')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_item_bookmarks');
    }
};
