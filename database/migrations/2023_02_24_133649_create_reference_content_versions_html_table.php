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
        Schema::create('reference_content_versions_html', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_content_version_id')->primary();
            $table->mediumText('html_content')->nullable();

            $table->foreign('reference_content_version_id', 'reference_content_version_id_foreign')
                ->references('id')
                ->on('reference_content_versions')
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
        Schema::table('reference_content_versions_html', function (Blueprint $table) {
            $table->dropForeign('reference_content_version_id_foreign');
        });
        Schema::dropIfExists('reference_content_versions_html');
    }
};
