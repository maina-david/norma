<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Enums\Corpus\MetaChangeStatus;
use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use Tests\TestCase;

class ReferenceMetadataControllerTest extends TestCase
{
    public function testCreatingShowingAndDeleting(): void
    {
        $this->signIn($this->collaborateSuperUser());

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create();

        $location = Location::factory()->create();
        $domain = LegalDomain::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $reference->locations()->attach($location->id);
        $reference->legalDomains()->attach($domain->id);

        $newLoc = Location::factory()->create();
        $newDom = LegalDomain::factory()->create();

        $parent = Reference::factory()->create(['work_id' => $expression->work_id]);
        $parent->locations()->attach($newLoc->id);
        $parent->legalDomains()->attach($newDom->id);

        $reference->update(['parent_id' => $parent->id]);

        /** @var Reference $reference */
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        Reference::factory()->create(['work_id' => $expression->work_id, 'parent_id' => $reference->id]);
        /** @var Location $location */
        $location = Location::factory()->create();
        /** @var LegalDomain $domain */
        $domain = LegalDomain::factory()->create();
        $domain->locations()->attach($location->id);
        $assessCategory = Category::factory()->create();
        $assessmentItem = AssessmentItem::factory()->create();
        $assessmentItem->categories()->attach($assessCategory->id);
        $contextQuestion = ContextQuestion::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $action = ActionArea::factory()->create();

        $this->getJson(route('api.collaborate.assessment-items.index.json', ['search' => $assessmentItem->component_3, 'location_id' => $location->id]))
            ->assertSuccessful()
            ->assertSee($assessmentItem->component_3);

        $this->getJson(route('api.collaborate.categories.index.json', ['search' => $category->display_label, 'location_id' => $location->id]))
            ->assertSuccessful()
            ->assertSee($category->display_label);

        $this->getJson(route('api.collaborate.categories.index.json', ['search' => $assessCategory->display_label, 'assess' => 1]))
            ->assertSuccessful()
            ->assertSee($assessCategory->display_label);

        $newLocation = Location::factory()->create();
        $this->getJson(route('api.collaborate.legal-domains.index.json', ['search' => $domain->title, 'location_id' => $newLocation->id]))
            ->assertSuccessful()
            ->assertDontSee($domain->title);
        $this->getJson(route('api.collaborate.legal-domains.index.json', ['search' => $domain->title, 'location_id' => $location->id]))
            ->assertSuccessful()
            ->assertSee($domain->title);

        $this->getJson(route('api.collaborate.context-questions.index.json', ['search' => $contextQuestion->predicate, 'location_id' => $location->id]))
            ->assertSuccessful()
            ->assertSee($contextQuestion->predicate);

        $this->getJson(route('api.collaborate.locations.index.json', ['search' => $location->title]))
            ->assertSuccessful()
            ->assertSee($location->title);

        $this->getJson(route('api.collaborate.tags.index.json', ['search' => $tag->title]))
            ->assertSuccessful()
            ->assertSee($tag->title);

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => $reference, 'relation' => 'actionAreas']), ['related' => [$action->id]])
            ->assertSuccessful();

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => $reference, 'relation' => 'legalDomains']), ['related' => [$domain->id]])
            ->assertSuccessful();

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => $reference, 'relation' => 'locations']), ['related' => [$location->id]])
            ->assertSuccessful();

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => $reference, 'relation' => 'assessmentItems']), ['related' => [$assessmentItem->id]])
            ->assertSuccessful();

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => $reference, 'relation' => 'contextQuestions']), ['related' => [$contextQuestion->id]])
            ->assertSuccessful();

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => $reference, 'relation' => 'categories']), ['related' => [$category->id]])
            ->assertSuccessful();

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => $reference, 'relation' => 'tags']), ['related' => [$tag->id]])
            ->assertSuccessful();

        $payload = [
            'expression' => $expression->id,
            'task' => '-',
            'include' => 'actionAreas|actionAreaDrafts|assessmentItems|assessmentItemDrafts|categories.categoryType|categoryDrafts.categoryType|contextQuestions|contextQuestionDrafts|legalDomains|legalDomainDrafts|locations|locationDrafts|tags|tagDrafts',
        ];

        $this->getJson(route('api.collaborate.work-expression.references.task.index', $payload))
            ->assertSuccessful();

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::ADD->value]);

        $this->deleteJson(route('api.collaborate.references.meta.destroy', ['reference' => $reference->id, 'relation' => 'legalDomains', 'related' => $domain->id]), [])
            ->assertSuccessful();
        $this->deleteJson(route('api.collaborate.references.meta.destroy', ['reference' => $reference->id, 'relation' => 'locations', 'related' => $location->id]), [])
            ->assertSuccessful();
        $this->deleteJson(route('api.collaborate.references.meta.destroy', ['reference' => $reference->id, 'relation' => 'assessmentItems', 'related' => $assessmentItem->id]), [])
            ->assertSuccessful();
        $this->deleteJson(route('api.collaborate.references.meta.destroy', ['reference' => $reference->id, 'relation' => 'contextQuestions', 'related' => $contextQuestion->id]), [])
            ->assertSuccessful();

        // test category removal, but with 'all' option
        $this->deleteJson(route('api.collaborate.references.meta.destroy', ['reference' => $reference->id, 'relation' => 'categories', 'related' => 'all']), [])
            ->assertSuccessful();
        $this->deleteJson(route('api.collaborate.references.meta.bulk.destroy', ['reference' => $reference->id, 'relation' => 'categories', '_delete_target' => 'all']), [])
            ->assertSuccessful();

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['reference_id' => $reference->id, 'legal_domain_id' => $domain->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $this->postJson(route('api.collaborate.references.meta.store', ['reference' => 'bulk', 'relation' => 'legalDomains']), ['references' => [$reference->id], 'related' => [$domain->id]])
            ->assertSuccessful();

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::ADD->value]);

        $this->deleteJson(route('api.collaborate.references.meta.bulk.destroy', ['relation' => 'legalDomains']), ['references' => [$reference->id], 'related' => [$domain->id]])
            ->assertSuccessful();

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $this->postJson(route('api.collaborate.references.meta-drafts.bulk.apply', ['relation' => 'legalDomains']), ['references' => [$reference->id]])
            ->assertSuccessful();

        $this->assertDatabaseMissing(LegalDomainReference::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id]);
        $this->assertDatabaseMissing(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $reference->legalDomains()->attach($domain);
        $this->deleteJson(route('api.collaborate.references.meta.bulk.destroy', ['relation' => 'legalDomains']), ['references' => [$reference->id], 'related' => [$domain->id]])
            ->assertSuccessful();

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $this->deleteJson(route('api.collaborate.references.meta-drafts.bulk.destroy', ['relation' => 'legalDomains']), ['references' => [$reference->id]])
            ->assertSuccessful();

        $this->assertDatabaseMissing(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $reference->legalDomainDrafts()->attach($domain);
        $this->deleteJson(route('api.collaborate.references.meta-drafts.destroy', ['reference' => $reference->id, 'relation' => 'legalDomains', 'related' => $domain->id]))
            ->assertSuccessful();
        $this->assertDatabaseMissing(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id]);
    }
}
