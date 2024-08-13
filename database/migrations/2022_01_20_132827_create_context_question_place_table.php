<?php

use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContextQuestionPlaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('context_question_place', function (Blueprint $table) {
            $table->unsignedInteger('context_question_id');
            $table->unsignedInteger('place_id');
            $table->tinyInteger('answer')->default(1);

            $table->primary(['context_question_id', 'place_id']);

            $table->foreign('context_question_id')
                ->references('id')
                ->on((new ContextQuestion())->getTable())
                ->onDelete('cascade');

            $table->foreign('place_id')
                ->references('id')
                ->on((new Libryo())->getTable())
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
        Schema::table('context_question_place', function (Blueprint $table) {
            $table->dropForeign(['context_question_id']);
            $table->dropForeign(['place_id']);
        });
        Schema::dropIfExists('context_question_place');
    }
}
