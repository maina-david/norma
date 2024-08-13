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
        Schema::create('legal_update_work', function (Blueprint $table) {
            $table->unsignedBigInteger('legal_update_id');
            $table->unsignedInteger('work_id');

            $table->primary(['legal_update_id', 'work_id']);
            $table->foreign('legal_update_id')->references('id')->on('corpus_legal_updates')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('corpus_works')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('legal_update_work');
    }
};
