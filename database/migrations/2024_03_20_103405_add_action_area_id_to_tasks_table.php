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
            $table->dropConstrainedForeignId('subject_category_id');
            $table->dropConstrainedForeignId('control_category_id');

            $table->unsignedBigInteger('action_area_id')->nullable()->after('org_identifier');
            $table->foreign('action_area_id')->references('id')->on('action_areas')->nullOnDelete();
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
            $table->dropConstrainedForeignId('action_area_id');

            $table->unsignedBigInteger('subject_category_id')->nullable()->after('org_identifier');
            $table->unsignedBigInteger('control_category_id')->nullable()->after('subject_category_id');

            $table->foreign('subject_category_id')->references('id')->on('corpus_categories')->nullOnDelete();
            $table->foreign('control_category_id')->references('id')->on('corpus_categories')->nullOnDelete();
        });
    }
};
