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
        Schema::create('librarian_project_tracking_specialist', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('user_id');

            $table->foreign('project_id')->references('id')->on('librarian_projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->primary(['project_id', 'user_id'], 'librarian_project_tracking_specialist_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('librarian_project_tracking_specialist');
    }
};
