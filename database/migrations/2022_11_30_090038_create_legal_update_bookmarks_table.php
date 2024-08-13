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
        Schema::create('legal_update_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legal_update_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('legal_update_id')->references('id')->on('corpus_legal_updates')->cascadeOnDelete();
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
        Schema::dropIfExists('legal_update_bookmarks');
    }
};
