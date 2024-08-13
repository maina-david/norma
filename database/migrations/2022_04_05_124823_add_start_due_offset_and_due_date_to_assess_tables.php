<?php

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
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
        Schema::table((new AssessmentItem())->getTable(), function (Blueprint $table) {
            $table->unsignedSmallInteger('start_due_offset')->nullable()->after('frequency');
        });
        Schema::table((new AssessmentItemResponse())->getTable(), function (Blueprint $table) {
            $table->timestamp('next_due_at')->nullable()->after('last_answered_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new AssessmentItem())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['start_due_offset']);
        });
        Schema::table((new AssessmentItemResponse())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['next_due_at']);
        });
    }
};
