<?php

use App\Enums\Corpus\MetaChangeStatus;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;
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
        Schema::table((new ReferenceTagDraft())->getTable(), function (Blueprint $table) {
            $table->unsignedTinyInteger('change_status')->default(MetaChangeStatus::ADD->value)->after('reference_id');
        });
        Schema::table((new CategoryReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->unsignedTinyInteger('change_status')->default(MetaChangeStatus::ADD->value)->after('reference_id');
        });
        Schema::table((new ContextQuestionReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->unsignedTinyInteger('change_status')->default(MetaChangeStatus::ADD->value)->after('reference_id');
        });
        Schema::table((new AssessmentItemReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->unsignedTinyInteger('change_status')->default(MetaChangeStatus::ADD->value)->after('reference_id');
        });
        Schema::table((new LegalDomainReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->unsignedTinyInteger('change_status')->default(MetaChangeStatus::ADD->value)->after('reference_id');
        });
        Schema::table((new LocationReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->unsignedTinyInteger('change_status')->default(MetaChangeStatus::ADD->value)->after('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new LocationReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['change_status']);
        });
        Schema::table((new LegalDomainReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['change_status']);
        });
        Schema::table((new AssessmentItemReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['change_status']);
        });
        Schema::table((new ContextQuestionReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['change_status']);
        });
        Schema::table((new CategoryReferenceDraft())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['change_status']);
        });
        Schema::table((new ReferenceTagDraft())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['change_status']);
        });
    }
};
