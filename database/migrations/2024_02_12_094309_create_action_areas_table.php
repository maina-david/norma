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
        Schema::create('action_areas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('control_category_id')->constrained('corpus_categories')->cascadeOnDelete();
            $table->foreignId('subject_category_id')->constrained('corpus_categories')->cascadeOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_areas');
    }
};
