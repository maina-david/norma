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
        Schema::create('compiled_libryo_reference', function (Blueprint $table) {
            $table->unsignedInteger('place_id');
            $table->unsignedBigInteger('reference_id');

            $table->primary(['place_id', 'reference_id']);
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete();
            $table->foreign('reference_id')->references('id')->on('corpus_references')->cascadeOnDelete();
        });
        DB::statement('INSERT IGNORE INTO compiled_libryo_reference (select place_id, reference_id from place_reference)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compiled_libryo_reference');
    }
};
