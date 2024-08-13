<?php

use App\Enums\Assess\AssessmentItemType;
use App\Models\Assess\AssessmentItem;
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
        Schema::table('corpus_assessment_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->after('id')->default(AssessmentItemType::LEGACY->value)->index();
        });

        AssessmentItem::withTrashed()->whereNotNull('organisation_id')->update(['type' => AssessmentItemType::USER_DEFINED->value]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_assessment_items', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
