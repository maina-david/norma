<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\BulkApplyTaggableDrafts;
use App\Enums\Corpus\MetaChangeStatus;
use App\Enums\Corpus\ReferenceType;
use App\Enums\Lookups\AnnotationSourceType;
use App\Models\Actions\ActionArea;
use App\Models\Actions\Pivots\ActionAreaReference;
use App\Models\Actions\Pivots\ActionAreaReferenceDraft;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Lookups\AnnotationSource;
use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryReference;
use Database\Seeders\Lookups\AnnotationSourcesSeeder;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class BulkApplyTaggableDraftsTest extends TestCase
{
    public function testHandle(): void
    {
        if (AnnotationSource::count() < 1) {
            $this->seed(AnnotationSourcesSeeder::class);
        }

        $work = Work::factory()->create();

        Config::set('assess.auto_apply_from_action_areas.locations.inherit', [$work->primary_location_id]);

        /** @var Reference $workRef */
        $workRef = Reference::factory()->create(['referenceable_type' => 'works', 'type' => ReferenceType::work()->value, 'work_id' => $work->id]);
        /** @var Reference $citationRef */
        $citationRef = Reference::factory()->create(['referenceable_type' => 'citations', 'parent_id' => $workRef->id, 'work_id' => $workRef->work_id]);
        /** @var Location $location */
        $location = Location::factory()->create();
        /** @var Location $locationToRemove */
        $locationToRemove = Location::factory()->create();
        /** @var ContextQuestion $question */
        $question = ContextQuestion::factory()->create();
        /** @var ActionArea $action */
        $action = ActionArea::factory()->create();
        /** @var AssessmentItem $aItem */
        $aItem = AssessmentItem::factory()->create(['action_area_id' => $action->id]);
        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();
        $question->categories()->attach($category);
        $aItem->categories()->attach($category2);

        $citationRef->locationDrafts()->attach($location, [
            'annotation_source_id' => AnnotationSourceType::MAGI->value,
            'change_status' => MetaChangeStatus::ADD->value,
        ]);
        $citationRef->contextQuestionDrafts()->attach($question, [
            'change_status' => MetaChangeStatus::ADD->value,
        ]);
        $citationRef->assessmentItemDrafts()->attach($aItem, [
            'change_status' => MetaChangeStatus::ADD->value,
        ]);
        $citationRef->locations()->attach($locationToRemove);
        $citationRef->locationDrafts()->attach($locationToRemove, [
            'annotation_source_id' => AnnotationSourceType::MAGI->value,
            'change_status' => MetaChangeStatus::REMOVE->value,
        ]);

        /** @var User $user */
        $user = User::factory()->create();
        $this->assertFalse($citationRef->locations->contains($location));
        app(BulkApplyTaggableDrafts::class)->handle(
            [$workRef->id],
            LocationReference::class,
            LocationReferenceDraft::class,
            $user
        );
        // apply context question and assessment item to test whether
        // related categories get auto-added
        app(BulkApplyTaggableDrafts::class)->handle(
            [$workRef->id],
            ContextQuestionReference::class,
            ContextQuestionReferenceDraft::class,
            $user
        );
        app(BulkApplyTaggableDrafts::class)->handle(
            [$workRef->id],
            AssessmentItemReference::class,
            AssessmentItemReferenceDraft::class,
            $user
        );

        $citationRef->actionAreaDrafts()->attach($action->id, ['change_status' => MetaChangeStatus::ADD->value]);

        $this->assertDatabaseMissing(CategoryReference::class, [
            'reference_id' => $citationRef->id,
            'category_id' => $action->control_category_id,
        ]);

        $this->assertDatabaseMissing(CategoryReference::class, [
            'reference_id' => $citationRef->id,
            'category_id' => $action->subject_category_id,
        ]);

        app(BulkApplyTaggableDrafts::class)->handle(
            [$citationRef->id],
            ActionAreaReference::class,
            ActionAreaReferenceDraft::class,
            $user
        );

        $this->assertDatabaseHas(CategoryReference::class, [
            'reference_id' => $citationRef->id,
            'category_id' => $action->control_category_id,
        ]);

        $this->assertDatabaseHas(CategoryReference::class, [
            'reference_id' => $citationRef->id,
            'category_id' => $action->subject_category_id,
        ]);

        $citationRef->refresh();
        $this->assertTrue($citationRef->locationDrafts->isEmpty());
        $this->assertTrue($citationRef->locations->contains($location));
        $this->assertFalse($citationRef->locations->contains($locationToRemove));
        $this->assertTrue($citationRef->contextQuestions->contains($question));
        // check that category was automatically added via context question
        $this->assertTrue($citationRef->categories->contains($category));
        // check that category was automatically added via assessment item
        $this->assertTrue($citationRef->categories->contains($category2));
        $this->assertSame(
            AnnotationSourceType::MAGI->value,
            $citationRef->locations()
                ->withPivot(['annotation_source_id'])
                ->first()
                ->pivot
                ->annotation_source_id
        );
        $this->assertSame(
            $user->id,
            $citationRef->locations()
                ->withPivot(['approved_by'])
                ->first()
                ->pivot
                ->approved_by
        );

        $this->assertSame(
            AnnotationSourceType::CONTEXT_QUESTION->value,
            $citationRef->categories()
                ->withPivot(['annotation_source_id'])
                ->where('category_id', $category->id)
                ->first()
                ->pivot
                ->annotation_source_id
        );
        $this->assertSame(
            AnnotationSourceType::ASSESSMENT_ITEM->value,
            $citationRef->categories()
                ->withPivot(['annotation_source_id'])
                ->where('category_id', $category2->id)
                ->first()
                ->pivot
                ->annotation_source_id
        );
    }
}
