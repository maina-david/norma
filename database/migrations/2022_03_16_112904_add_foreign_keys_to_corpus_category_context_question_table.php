<?php

use App\Models\Compilation\ContextQuestion;
use App\Models\Ontology\Category;
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
        Schema::table('corpus_category_context_question', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->change();

            $table->foreign('category_id')
                ->references('id')
                ->on((new Category())->getTable())
                ->cascadeOnDelete();

            $table->foreign('context_question_id')
                ->references('id')
                ->on((new ContextQuestion())->getTable())
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_category_context_question', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['context_question_id']);
        });
        Schema::table('corpus_category_context_question', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->change();
        });
    }
};
