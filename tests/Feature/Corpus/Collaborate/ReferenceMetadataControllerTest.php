<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Enums\Corpus\MetaChangeStatus;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
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
        /** @var LegalDomain $domain */
        $domain = LegalDomain::factory()->create();
        /** @var Location $location */
        $location = Location::factory()->create();
        $assessCategory = Category::factory()->create();
        $assessmentItem = AssessmentItem::factory()->create();
        $assessmentItem->categories()->attach($assessCategory->id);
        $contextQuestion = ContextQuestion::factory()->create();
        $category = Category::factory()->create();

        $meta = implode('|', ['assessments', 'topics', 'context', 'domains', 'jurisdictions', 'tags']);

        $this->turboGet(route('collaborate.work-expressions.references.meta.frame', ['expression' => $expression->id]))
            ->assertSee('meta_togglers');

        $this->turboGet(route('collaborate.work-expressions.references.meta.index', ['expression' => $expression->id, 'show' => $meta]))
            ->assertSee("meta_{$reference->id}");

        $this->turboGet(route('collaborate.corpus.references.meta.create', ['expression' => $expression->id, 'reference' => $reference, 'relation' => 'legalDomains']))
            ->assertSee("meta_{$reference->id}_attach");

        $this->turboGet(route('collaborate.corpus.references.meta.bulk.show', ['expression' => $expression->id, 'reference' => $reference, 'relation' => 'legalDomains']), ['turbo-frame' => 'awesome-bulks'])
            ->assertSee('awesome-bulks');

        $response = $this->turboPost(route('collaborate.corpus.references.meta.store', ['expression' => $expression->id, 'reference' => $reference, 'relation' => 'legalDomains']), ['related' => [$domain->id]])
            ->assertRedirect();

        $this->turboGet($response->headers->get('Location'))->assertSuccessful();

        $this->turboPost(route('collaborate.corpus.references.meta.store', ['expression' => $expression->id, 'reference' => $reference, 'relation' => 'locations']), ['related' => [$location->id]])
            ->assertRedirect();

        $this->turboPost(route('collaborate.corpus.references.meta.store', ['expression' => $expression->id, 'reference' => $reference, 'relation' => 'assessmentItems']), ['related' => [$assessmentItem->id]])
            ->assertRedirect();

        $this->turboPost(route('collaborate.corpus.references.meta.store', ['expression' => $expression->id, 'reference' => $reference, 'relation' => 'contextQuestions']), ['related' => [$contextQuestion->id]])
            ->assertRedirect();

        $this->turboPost(route('collaborate.corpus.references.meta.store', ['expression' => $expression->id, 'reference' => $reference, 'relation' => 'categories']), ['related' => [$category->id]])
            ->assertRedirect();

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::ADD->value]);

        $this->get(route('collaborate.corpus.references.meta.show', ['expression' => $expression->id, 'show' => 'assessments', 'reference' => $reference->id]), ['turbo-frame' => 'test'])
            ->assertSuccessful()
            ->assertSee($domain->title);

        $this->delete(route('collaborate.corpus.references.meta.destroy', ['expression' => $expression->id, 'reference' => $reference->id, 'relation' => 'legalDomains', 'related' => $domain->id]), [], ['turbo-frame' => 'test'])
            ->assertRedirect();
        $this->delete(route('collaborate.corpus.references.meta.destroy', ['expression' => $expression->id, 'reference' => $reference->id, 'relation' => 'locations', 'related' => $location->id]), [], ['turbo-frame' => 'test'])
            ->assertRedirect();
        $this->delete(route('collaborate.corpus.references.meta.destroy', ['expression' => $expression->id, 'reference' => $reference->id, 'relation' => 'assessmentItems', 'related' => $assessmentItem->id]), [], ['turbo-frame' => 'test'])
            ->assertRedirect();
        $this->delete(route('collaborate.corpus.references.meta.destroy', ['expression' => $expression->id, 'reference' => $reference->id, 'relation' => 'contextQuestions', 'related' => $contextQuestion->id]), [], ['turbo-frame' => 'test'])
            ->assertRedirect();
        // test category removal, but with 'all' option
        $this->delete(route('collaborate.corpus.references.meta.destroy', ['expression' => $expression->id, 'reference' => $reference->id, 'relation' => 'categories', 'related' => 'all']), [], ['turbo-frame' => 'test'])
            ->assertRedirect();
        $this->delete(route('collaborate.corpus.references.meta.bulk.destroy', ['expression' => $expression->id, 'reference' => $reference->id, 'relation' => 'categories', '_delete_target' => 'all']), [], ['turbo-frame' => 'test'])
            ->assertSuccessful();

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['reference_id' => $reference->id, 'legal_domain_id' => $domain->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $this->get(route('collaborate.corpus.references.meta.show', ['expression' => $expression->id, 'show' => 'assessments', 'reference' => $reference->id]), ['turbo-frame' => 'test'])
            ->assertSuccessful();

        $this->turboPost(route('collaborate.corpus.references.meta.store', ['expression' => $expression->id, "reference_{$reference->id}" => true, 'reference' => 'bulk', 'relation' => 'legalDomains']), ['related' => [$domain->id]])
            ->assertSuccessful()
            ->assertSee("meta_{$reference->id}");

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::ADD->value]);

        $this->turboDelete(route('collaborate.corpus.references.meta.bulk.destroy', ['expression' => $expression->id, "reference_{$reference->id}" => true, 'relation' => 'legalDomains']), ['related' => [$domain->id]])
            ->assertSuccessful()
            ->assertSee("meta_{$reference->id}");

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $this->turboPost(route('collaborate.corpus.references.meta-drafts.bulk.apply', ['expression' => $expression->id, "reference_{$reference->id}" => true, 'relation' => 'legalDomains']))
            ->assertSuccessful()
            ->assertSee("meta_{$reference->id}");

        $this->assertDatabaseMissing(LegalDomainReference::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id]);
        $this->assertDatabaseMissing(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $reference->legalDomains()->attach($domain);
        $this->turboDelete(route('collaborate.corpus.references.meta.bulk.destroy', ['expression' => $expression->id, "reference_{$reference->id}" => true, 'relation' => 'legalDomains']), ['related' => [$domain->id]])
            ->assertSuccessful()
            ->assertSee("meta_{$reference->id}");

        $this->assertDatabaseHas(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $this->turboDelete(route('collaborate.corpus.references.meta-drafts.bulk.destroy', ['expression' => $expression->id, "reference_{$reference->id}" => true, 'relation' => 'legalDomains']))
            ->assertSuccessful()
            ->assertSee("meta_{$reference->id}");

        $this->assertDatabaseMissing(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id, 'change_status' => MetaChangeStatus::REMOVE->value]);

        $reference->legalDomainDrafts()->attach($domain);
        $this->turboDelete(route('collaborate.corpus.references.meta-drafts.destroy', ['expression' => $expression->id, 'reference' => $reference->id, 'relation' => 'legalDomains', 'related' => $domain->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing(LegalDomainReferenceDraft::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id]);
    }
}
