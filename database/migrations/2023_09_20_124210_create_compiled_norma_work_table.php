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
        Schema::create('compiled_norma_work', function (Blueprint $table) {
            $table->unsignedInteger('place_id');
            $table->unsignedInteger('work_id');

            $table->primary(['place_id', 'work_id']);
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('corpus_works')->cascadeOnDelete();
        });

        DB::statement('INSERT IGNORE INTO compiled_norma_work (select place_id, work_id from place_work)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compiled_norma_work');
    }
};
