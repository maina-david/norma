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
        Schema::create('identity_providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('organisation_id');
            $table->unsignedInteger('team_id')->nullable();
            $table->text('entity_id');
            $table->text('sso_url');
            $table->text('slo_url')->nullable();
            $table->longText('certificate');
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->foreign('organisation_id')->references('id')->on('organisations')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('identity_providers');
    }
};
