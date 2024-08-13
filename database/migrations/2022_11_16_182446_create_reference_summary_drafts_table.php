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
        Schema::create('reference_summary_drafts', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->primary();
            $table->text('summary_body')->nullable();
            $table->unsignedInteger('author_id')->nullable();
            $table->timestamps();

            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('reference_id')
                ->references('id')
                ->on('corpus_references')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reference_summary_drafts', function (Blueprint $table) {
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['author_id']);
        });
        Schema::dropIfExists('reference_summary_drafts');
    }
};
