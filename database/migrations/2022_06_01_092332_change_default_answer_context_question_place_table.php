<?php

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Auth\User;
use App\Models\Customer\Pivots\ContextQuestionLibryo;
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
        Schema::table((new ContextQuestionLibryo())->getTable(), function (Blueprint $table) {
            // tinyinteger doesn't seem to work
            $table->boolean('answer')->default(ContextQuestionAnswer::maybe()->value)->change();
            $table->unsignedInteger('last_answered_by')->nullable();
            $table->timestamp('last_answered_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('last_answered_by')
                ->references('id')
                ->on((new User())->getTable())
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
        Schema::table((new ContextQuestionLibryo())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['last_answered_at']);
            $table->dropForeign(['last_answered_by']);
            $table->dropColumn(['last_answered_by']);
            $table->boolean('answer')->default(ContextQuestionAnswer::yes()->value)->change();
        });
    }
};
