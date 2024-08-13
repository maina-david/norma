<?php

use App\Models\Compilation\Pivots\ContextQuestionReference;
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
        Schema::table((new ContextQuestionReference())->getTable(), function (Blueprint $table) {
            $table->string('source')->nullable()->after('reference_id');
            $table->timestamp('applied_at')->nullable()->after('source')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new ContextQuestionReference())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['applied_at']);
            $table->dropColumn(['source']);
        });
    }
};
