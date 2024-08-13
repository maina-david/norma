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
        Schema::create('action_area_compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_area_id')->constrained('action_areas')->onDelete('cascade');
            $table->integer('risk_of_non_compliance')->default(4);
            $table->dateTime('date_answered')->nullable();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dateTime('next_review')->nullable();
            $table->boolean('current')->default(true);
            $table->softDeletes();
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
        Schema::dropIfExists('action_area_compliances');
    }
};
