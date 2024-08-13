<?php

use App\Models\Compilation\ContextQuestion;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\ContextQuestionLegalUpdate;
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
        Schema::create((new ContextQuestionLegalUpdate())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('context_question_id');
            $table->unsignedBigInteger('legal_update_id');
            $table->string('source')->nullable();
            $table->timestamp('applied_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            $table->primary(['context_question_id', 'legal_update_id'], 'context_question_legal_update_primary');

            $table->foreign('context_question_id')
                ->references('id')
                ->on((new ContextQuestion())->getTable())
                ->onDelete('cascade');

            $table->foreign('legal_update_id')
                ->references('id')
                ->on((new LegalUpdate())->getTable())
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
        Schema::table((new ContextQuestionLegalUpdate())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['context_question_id']);
            $table->dropForeign(['legal_update_id']);
        });
        Schema::dropIfExists((new ContextQuestionLegalUpdate())->getTable());
    }
};
