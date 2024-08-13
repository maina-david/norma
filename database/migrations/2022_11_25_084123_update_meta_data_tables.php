<?php

use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Pivots\ReferenceTag;
use App\Models\Lookups\AnnotationSource;
use App\Models\Ontology\Pivots\CategoryReference;
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
        Schema::table((new ReferenceTag())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('applied_by')->nullable()->after('reference_id');
            $table->unsignedInteger('approved_by')->nullable()->after('applied_by');
            $table->unsignedInteger('annotation_source_id')->nullable()->after('approved_by');

            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('annotation_source_id')
                ->references('id')
                ->on((new AnnotationSource())->getTable())
                ->onDelete('set null');
        });
        Schema::table((new AssessmentItemReference())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('applied_by')->nullable()->after('reference_id');
            $table->unsignedInteger('approved_by')->nullable()->after('applied_by');
            $table->unsignedInteger('annotation_source_id')->nullable()->after('approved_by');

            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('annotation_source_id')
                ->references('id')
                ->on((new AnnotationSource())->getTable())
                ->onDelete('set null');
        });
        Schema::table((new CategoryReference())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('applied_by')->nullable()->after('reference_id');
            $table->unsignedInteger('approved_by')->nullable()->after('applied_by');
            $table->unsignedInteger('annotation_source_id')->nullable()->after('approved_by');

            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('annotation_source_id')
                ->references('id')
                ->on((new AnnotationSource())->getTable())
                ->onDelete('set null');
        });
        Schema::table((new ContextQuestionReference())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('applied_by')->nullable()->after('reference_id');
            $table->unsignedInteger('approved_by')->nullable()->after('applied_by');
            $table->unsignedInteger('annotation_source_id')->nullable()->after('approved_by');

            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('annotation_source_id')
                ->references('id')
                ->on((new AnnotationSource())->getTable())
                ->onDelete('set null');
        });
        Schema::table((new LegalDomainReference())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('applied_by')->nullable()->after('reference_id');
            $table->unsignedInteger('approved_by')->nullable()->after('applied_by');
            $table->unsignedInteger('annotation_source_id')->nullable()->after('approved_by');

            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('annotation_source_id')
                ->references('id')
                ->on((new AnnotationSource())->getTable())
                ->onDelete('set null');
        });
        Schema::table((new LocationReference())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('applied_by')->nullable()->after('reference_id');
            $table->unsignedInteger('approved_by')->nullable()->after('applied_by');
            $table->unsignedInteger('annotation_source_id')->nullable()->after('approved_by');
            $table->timestamp('applied_at')->nullable()->after('annotation_source_id')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('annotation_source_id')
                ->references('id')
                ->on((new AnnotationSource())->getTable())
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
        Schema::table((new LocationReference())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);

            $table->dropColumn(['annotation_source_id']);
            $table->dropColumn(['approved_by']);
            $table->dropColumn(['applied_by']);
            $table->dropColumn(['applied_at']);
        });
        Schema::table((new LegalDomainReference())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);

            $table->dropColumn(['annotation_source_id']);
            $table->dropColumn(['approved_by']);
            $table->dropColumn(['applied_by']);
        });
        Schema::table((new ContextQuestionReference())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);

            $table->dropColumn(['annotation_source_id']);
            $table->dropColumn(['approved_by']);
            $table->dropColumn(['applied_by']);
        });
        Schema::table((new CategoryReference())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);

            $table->dropColumn(['annotation_source_id']);
            $table->dropColumn(['approved_by']);
            $table->dropColumn(['applied_by']);
        });
        Schema::table((new AssessmentItemReference())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);

            $table->dropColumn(['annotation_source_id']);
            $table->dropColumn(['approved_by']);
            $table->dropColumn(['applied_by']);
        });
        Schema::table((new ReferenceTag())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);

            $table->dropColumn(['annotation_source_id']);
            $table->dropColumn(['approved_by']);
            $table->dropColumn(['applied_by']);
        });
    }
};
