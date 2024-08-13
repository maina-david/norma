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
        Schema::table('update_candidates', function (Blueprint $table) {
            $table->unsignedInteger('checked_by_id')->nullable()->after('checked_comment');
            $table->unsignedInteger('author_id')->nullable()->after('doc_id');
            $table->foreign('checked_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            $table->dropColumn(['affected_legislation_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('update_candidates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
            $table->unsignedTinyInteger('affected_legislation_type')->after('justification')->nullable();
        });
    }
};
