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
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedMediumInteger('impact')->nullable()->after('priority');
            $table->unsignedBigInteger('reference_content_extract_id')->nullable()->after('impact');

            $table->foreign('reference_content_extract_id')
                ->references('id')
                ->on('reference_content_extracts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reference_content_extract_id');
            $table->dropColumn(['impact']);
        });
    }
};
