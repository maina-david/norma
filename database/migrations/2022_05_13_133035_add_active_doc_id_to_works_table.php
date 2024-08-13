<?php

use App\Models\Corpus\Doc;
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
        Schema::table('corpus_works', function (Blueprint $table) {
            $table->unsignedBigInteger('active_doc_id')->nullable()->after('active_work_expression_id');

            $table->foreign('active_doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_works', function (Blueprint $table) {
            $table->dropForeign(['active_doc_id']);

            $table->dropColumn(['active_doc_id']);
        });
    }
};
