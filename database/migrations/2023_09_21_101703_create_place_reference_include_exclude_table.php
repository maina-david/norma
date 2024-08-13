<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('place_reference_include_exclude', function (Blueprint $table) {
            $table->unsignedInteger('place_id');
            $table->unsignedBigInteger('reference_id');
            $table->boolean('include');

            $table->primary(['place_id', 'reference_id']);
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete();
            $table->foreign('reference_id')->references('id')->on('corpus_references')->cascadeOnDelete();
        });

        $select = '
            select places.id as place_id, register_item_id as reference_id, include_exclude as include 
            from library_register_item_include_exclude 
            inner join places on places.library_id = library_register_item_include_exclude.library_id
        ';

        DB::statement("INSERT IGNORE INTO place_reference_include_exclude ({$select})");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('place_reference_include_exclude');
    }
};
