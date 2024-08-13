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
        Schema::create('legal_domain_location', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legal_domain_id');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('owner_id')->nullable();

            $table->foreign('legal_domain_id')->references('id')->on('corpus_legal_domains')->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on('corpus_locations')->cascadeOnDelete();
            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('legal_domain_location');
    }
};
