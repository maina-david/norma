<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;
use App\Models\Ontology\Tag;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReferenceActionsControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testTriggeringActions(): void
    {
        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create();
        /** @var Collection $references */
        $references = Reference::factory()->count(2)->create([
            'work_id' => $expression->work_id,
            'status' => ReferenceStatus::active(),
        ]);
        foreach ($references as $reference) {
            Summary::factory()->create(['reference_id' => $reference->id]);
            $reference->refRequirement?->delete();
        }

        $route = route('collaborate.work-expressions.references.actions', ['expression' => $expression->id]);

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->setExpression($expression);

        $payload = $references->mapWithKeys(fn ($item) => ["ref_{$item->id}" => true])->toArray();

        $references->load(['refRequirement', 'requirementDraft']);
        $references->each(fn ($ref) => $this->assertNull($ref->refresh()->requirementDraft));
        $references->each(fn ($ref) => $this->assertNull($ref->refRequirement));

        /** @var Reference $tocRef */
        $tocRef = $references->first();
        $doc = Doc::factory()->create(['work_id' => $tocRef->work_id]);
        /** @var Work $work */
        $work = Work::find($tocRef->work_id);
        $work->update(['active_doc_id' => $doc->id]);
        $matchingToc = TocItem::factory()->create([
            'label' => $tocRef->refPlainText->plain_text,
            'doc_id' => $doc->id,
            'uid' => hex2bin(sha1('testing')),
        ]);

        $this->assertNull($tocRef->refresh()->uid);

        $this->assertDatabaseHas(Reference::class, ['id' => $tocRef->id, 'uid' => null]);

        $this->turboPost($route, ['action' => 'match-tocs'])->assertRedirect();

        $this->assertDatabaseMissing(Reference::class, ['id' => $tocRef->id, 'uid' => null]);

        $this->assertNotNull($tocRef->refresh()->uid);

        $this->turboPost($route, ['action' => 'request-requirement', ...$payload])
            ->assertRedirect();

        $references->load(['refRequirement', 'requirementDraft']);
        $references->each(fn ($ref) => $this->assertNotNull($ref->refresh()->requirementDraft));
        $references->each(fn ($ref) => $this->assertNull($ref->refRequirement));

        $this->turboPost($route, ['action' => 'delete-requirement-drafts', ...$payload])
            ->assertRedirect();

        $references->load(['refRequirement', 'requirementDraft']);
        $references->each(fn ($ref) => $this->assertNull($ref->refresh()->requirementDraft));
        $references->each(fn ($ref) => $this->assertNull($ref->refRequirement));

        $this->turboPost($route, ['action' => 'request-requirement', ...$payload])
            ->assertRedirect();

        $this->turboPost(route('collaborate.work-expressions.references.actions.check', ['expression' => $expression->id]), [
            'action' => 'apply-requirement-drafts',
            ...$payload,
            'label_suffix' => 'apply_requirement_drafts',
        ])
            ->assertSuccessful()
            ->assertSee('Apply incomplete drafts');

        $this->turboPost($route, ['action' => 'apply-requirement-drafts', ...$payload])
            ->assertRedirect();

        $references->load(['refRequirement', 'requirementDraft']);
        $references->each(fn ($ref) => $this->assertNotNull($ref->refresh()->refRequirement));
        $references->each(fn ($ref) => $this->assertNull($ref->requirementDraft));

        $this->turboPost($route, ['action' => 'delete-requirement', ...$payload])
            ->assertRedirect();

        $references->load(['refRequirement', 'requirementDraft']);
        $references->each(fn ($ref) => $this->assertNull($ref->refresh()->refRequirement));
        $references->each(fn ($ref) => $this->assertNull($ref->requirementDraft));

        $references->each(fn ($ref) => $this->assertTrue(ReferenceStatus::active()->is($ref->refresh()->status)));

        $this->turboPost($route, ['action' => 'request-changes', ...$payload])
            ->assertRedirect();

        $references->each(fn ($ref) => $this->assertTrue(ReferenceStatus::pending()->is($ref->refresh()->status)));

        $redirect = $this->turboPost($route, ['action' => 'apply-drafts', ...$payload])
            ->assertRedirect();

        $this->followRedirects($redirect)->assertSuccessful()->assertSee($references->first()->id);

        $references->each(fn ($ref) => $this->assertTrue(ReferenceStatus::active()->is($ref->refresh()->status)));

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $references->first()->id]);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $references->first()->id]);

        $this->turboPost($route, ['action' => 'request-summary-changes', ...$payload])
            ->assertRedirect();

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $references->first()->id]);

        $this->turboPost($route, ['action' => 'apply-summary-drafts', ...$payload])
            ->assertRedirect();

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $references->first()->id]);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $references->first()->id]);

        $this->turboPost($route, ['action' => 'request-summary-changes', ...$payload])
            ->assertRedirect();

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $references->first()->id]);

        $this->turboPost($route, ['action' => 'delete-summary-drafts', ...$payload])
            ->assertRedirect();

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $references->first()->id]);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $references->first()->id]);

        $this->turboPost($route, ['action' => 'delete-summaries', ...$payload])
            ->assertRedirect();

        $this->assertDatabaseMissing(Summary::class, ['reference_id' => $references->first()->id]);

        $this->turboPost($route, ['action' => 'request-summary-changes', ...$payload])
            ->assertRedirect();

        // also test where no summary available yet
        $this->turboPost($route, ['action' => 'apply-summary-drafts', ...$payload])
            ->assertRedirect();

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $references->first()->id]);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $references->first()->id]);

        /** @var AssessmentItem */
        $aItem = AssessmentItem::factory()->create();
        /** @var ContextQuestion */
        $question = ContextQuestion::factory()->create();
        /** @var LegalDomain */
        $domain = LegalDomain::factory()->create();
        /** @var Tag */
        $tag = Tag::factory()->create();
        /** @var Location */
        $location = Location::factory()->create();
        /** @var Category */
        $category = Category::factory()->create();
        /** @var Reference */
        $reference = $references->first();

        $reference->assessmentItemDrafts()->attach($aItem);
        $reference->contextQuestionDrafts()->attach($question);
        $reference->legalDomainDrafts()->attach($domain);
        $reference->tagDrafts()->attach($tag);
        $reference->locationDrafts()->attach($location);
        $reference->categoryDrafts()->attach($category);

        $this->turboPost($route, ['action' => 'apply-assessment-item-drafts', ...$payload])
            ->assertRedirect();
        $this->assertDatabaseMissing(AssessmentItemReferenceDraft::class, ['reference_id' => $reference->id, 'assessment_item_id' => $aItem->id]);
        $this->turboPost($route, ['action' => 'apply-context-question-drafts', ...$payload])
            ->assertRedirect();
        $this->assertDatabaseMissing(ContextQuestionReferenceDraft::class, ['reference_id' => $reference->id, 'context_question_id' => $question->id]);
        $this->turboPost($route, ['action' => 'apply-legal-domain-drafts', ...$payload])
            ->assertRedirect();
        $this->assertDatabaseMissing(LegalDomainReferenceDraft::class, ['reference_id' => $reference->id, 'legal_domain_id' => $domain->id]);
        $this->turboPost($route, ['action' => 'apply-category-drafts', ...$payload])
            ->assertRedirect();
        $this->assertDatabaseMissing(CategoryReferenceDraft::class, ['reference_id' => $reference->id, 'category_id' => $category->id]);
        $this->turboPost($route, ['action' => 'apply-location-drafts', ...$payload])
            ->assertRedirect();
        $this->assertDatabaseMissing(LocationReferenceDraft::class, ['reference_id' => $reference->id, 'location_id' => $location->id]);
        $this->turboPost($route, ['action' => 'apply-tag-drafts', ...$payload])
            ->assertRedirect();
        $this->assertDatabaseMissing(ReferenceTagDraft::class, ['reference_id' => $reference->id, 'tag_id' => $tag->id]);

        $this->turboPost($route, ['action' => 'delete-references', ...$payload])
            ->assertRedirect();

        $this->turboPost($route, ['action' => 'generate-extracts', ...$payload])
            ->assertRedirect();

        $this->turboPost($route, ['action' => 'delete-extracts', ...$payload])
            ->assertRedirect();

        $this->assertDatabaseMissing(Reference::class, ['type' => ReferenceType::citation()->value, 'work_id' => $expression->work_id, 'deleted_at' => null]);
    }
}
