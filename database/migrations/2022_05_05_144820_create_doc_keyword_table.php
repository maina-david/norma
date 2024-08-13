<?php

use App\Models\Corpus\Doc;
use App\Models\Corpus\Keyword;
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
        Schema::create('doc_keyword', function (Blueprint $table) {
            $table->unsignedBigInteger('doc_id');
            $table->unsignedBigInteger('keyword_id');
            $table->float('score')->nullable();

            $table->primary(['doc_id', 'keyword_id']);

            $table->foreign('doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
                ->onDelete('cascade');

            $table->foreign('keyword_id')
                ->references('id')
                ->on((new Keyword())->getTable())
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
        Schema::table('doc_keyword', function (Blueprint $table) {
            $table->dropForeign(['doc_id']);
            $table->dropForeign(['keyword_id']);
        });
        Schema::dropIfExists('doc_keyword');
    }
};
