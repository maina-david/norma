<?php

use App\Models\Corpus\Doc;
use App\Models\Corpus\WorkExpression;
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
        Schema::table((new WorkExpression())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('doc_id')->nullable()->after('source_document_id');

            $table->foreign('doc_id')
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
        Schema::table((new WorkExpression())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['doc_id']);
            $table->dropColumn(['doc_id']);
        });
    }
};
