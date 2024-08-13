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
        Schema::table('librarian_boards', function (Blueprint $table) {
            $table->boolean('for_legal_update')->default(false)->after('source_document_required');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('librarian_boards', function (Blueprint $table) {
            $table->dropColumn(['for_legal_update']);
        });
    }
};
