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
        Schema::table('notification_handovers', function (Blueprint $table) {
            $table->unsignedInteger('board_id')->nullable()->after('text');

            $table->foreign('board_id')->references('id')->on('librarian_boards')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_handovers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('board_id');
        });
    }
};
