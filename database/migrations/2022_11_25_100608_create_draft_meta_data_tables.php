<?php

use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Geonames\Location;
use App\Models\Lookups\AnnotationSource;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
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
        Schema::create('location_reference_draft', function (Blueprint $table) {
            $table->unsignedInteger('location_id');
            $table->unsignedBigInteger('reference_id');
            $table->primary(['location_id', 'reference_id'], 'location_reference_draft_primary');

            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();
            $table->timestamp('applied_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('location_id')
                ->references('id')
                ->on((new Location())->getTable())
                ->cascadeOnDelete();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->cascadeOnDelete();

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
        Schema::create('legal_domain_reference_draft', function (Blueprint $table) {
            $table->unsignedInteger('legal_domain_id');
            $table->unsignedBigInteger('reference_id');
            $table->primary(['legal_domain_id', 'reference_id'], 'legal_domain_reference_draft_primary');

            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();
            $table->timestamp('applied_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('legal_domain_id')
                ->references('id')
                ->on((new LegalDomain())->getTable())
                ->cascadeOnDelete();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->cascadeOnDelete();

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
        Schema::create('context_question_reference_draft', function (Blueprint $table) {
            $table->unsignedInteger('context_question_id');
            $table->unsignedBigInteger('reference_id');
            $table->primary(['context_question_id', 'reference_id'], 'context_question_reference_draft_primary');

            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();
            $table->timestamp('applied_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('context_question_id')
                ->references('id')
                ->on((new ContextQuestion())->getTable())
                ->cascadeOnDelete();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->cascadeOnDelete();

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
        Schema::create('assessment_item_reference_draft', function (Blueprint $table) {
            $table->unsignedInteger('assessment_item_id');
            $table->unsignedBigInteger('reference_id');
            $table->primary(['assessment_item_id', 'reference_id'], 'assessment_item_reference_draft_primary');

            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();
            $table->timestamp('applied_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('assessment_item_id')
                ->references('id')
                ->on((new AssessmentItem())->getTable())
                ->cascadeOnDelete();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->cascadeOnDelete();

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
        Schema::create('category_reference_draft', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('reference_id');
            $table->primary(['category_id', 'reference_id']);

            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();
            $table->timestamp('applied_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('category_id')
                ->references('id')
                ->on((new Category())->getTable())
                ->cascadeOnDelete();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->cascadeOnDelete();

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
        Schema::create('reference_tag_draft', function (Blueprint $table) {
            $table->unsignedInteger('tag_id');
            $table->unsignedBigInteger('reference_id');
            $table->primary(['tag_id', 'reference_id']);

            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();
            $table->timestamp('applied_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('tag_id')
                ->references('id')
                ->on((new Tag())->getTable())
                ->cascadeOnDelete();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->cascadeOnDelete();

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
        Schema::table('reference_tag_draft', function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['tag_id']);
        });
        Schema::dropIfExists('reference_tag_draft');

        Schema::table('category_reference_draft', function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['category_id']);
        });
        Schema::dropIfExists('category_reference_draft');

        Schema::table('assessment_item_reference_draft', function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['assessment_item_id']);
        });
        Schema::dropIfExists('assessment_item_reference_draft');

        Schema::table('context_question_reference_draft', function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['context_question_id']);
        });
        Schema::dropIfExists('context_question_reference_draft');

        Schema::table('legal_domain_reference_draft', function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['legal_domain_id']);
        });
        Schema::dropIfExists('legal_domain_reference_draft');

        Schema::table('location_reference_draft', function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['location_id']);
        });
        Schema::dropIfExists('location_reference_draft');
    }
};
