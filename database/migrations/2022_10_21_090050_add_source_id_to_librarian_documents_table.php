<?php

use App\Models\Arachno\Source;
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
        Schema::table('librarian_documents', function (Blueprint $table) {
            $table->unsignedInteger('source_id')->nullable()->after('work_expression_id');

            $table->foreign('source_id')
                ->references('id')
                ->on((new Source())->getTable())
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
        Schema::table('librarian_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_id');
        });
    }
};
