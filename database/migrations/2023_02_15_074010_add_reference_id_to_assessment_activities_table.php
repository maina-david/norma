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
        Schema::table('assessment_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->nullable()->after('file_id');

            $table->foreign('reference_id')->references('id')->on('corpus_references')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assessment_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reference_id');
        });
    }
};
