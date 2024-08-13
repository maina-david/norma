<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Enums\Corpus\MetaItemStatus;
use App\Enums\Corpus\ReferenceType;
use App\Enums\Workflows\CollaborateSessionKey;
use App\Http\Controllers\Traits\UsesAnnotationPage;
use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use Tests\TestCase;

class WorkExpressionReferencesTest extends TestCase
{
    use UsesAnnotationPage;

    /**
     * @return void
     */
    public function testProvidesCorrectListing(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $work = Work::factory()->create();
        $expression = WorkExpression::factory()->create(['work_id' => $work->id]);
        $references = Reference::factory()
            ->count($this->perPage)
            ->sequence(fn ($sequence) => ['start' => $sequence->index + 1])
            ->create([
                'referenceable_id' => 1,
                'work_id' => $work->id,
                'type' => ReferenceType::citation(),
                'volume' => 1,
            ])
            ->sortBy('start')
            ->values();
        $references->load('refPlainText');

        $this->setExpression($expression);

        $getRoute = function ($params = []) use ($expression) {
            $response = $this->turboGet(route('collaborate.work-expressions.references.filters', ['expression' => $expression->id, ...$params]));

            return $this->turboGet($response->headers->get('Location'));
        };

        $filters = [
            'expression' => $expression->id,
            'has-actions' => 0,
            'has-assessments' => 0,
            'has-topics' => 0,
            'has-context' => 0,
            'has-domains' => 0,
            'has-jurisdictions' => 0,
            'has-tags' => 0,
            'has-links' => 0,
            'has-comments' => 0,
        ];

        $getRoute($filters)
            ->assertSuccessful()
            ->assertDontSee('Related');

        $getRoute(['has-summary' => MetaItemStatus::PENDING->value, 'has-requirement' => MetaItemStatus::PENDING->value])
            ->assertSuccessful()
            ->assertDontSee('Related');
        $getRoute(['has-summary' => MetaItemStatus::COMPLETED->value, 'has-requirement' => MetaItemStatus::COMPLETED->value])
            ->assertSuccessful()
            ->assertDontSee('Related');
        $getRoute(['has-summary' => MetaItemStatus::ANY->value, 'has-requirement' => MetaItemStatus::ANY->value])
            ->assertSuccessful()
            ->assertDontSee('Related');
        $getRoute(['has-summary' => MetaItemStatus::ANY->value, 'has-consequence' => MetaItemStatus::ANY->value])
            ->assertSuccessful()
            ->assertDontSee('Related');

        // test ReferenceFilter has methods without a value
        $q = Reference::whereKey($references->modelKeys());
        $this->assertSame(0, $q->filter(['has-actions' => 0])->count());
        $this->assertSame(0, $q->filter(['has-tags' => 0])->count());
        $this->assertSame(0, $q->filter(['has-asssessments' => 0])->count());
        $this->assertSame(0, $q->filter(['has-topics' => 0])->count());
        $this->assertSame(0, $q->filter(['has-context' => 0])->count());
        $this->assertSame(0, $q->filter(['has-domains' => 0])->count());
        $this->assertSame(0, $q->filter(['has-jurisdictions' => 0])->count());

        $getRoute([
            'has-actions' => MetaItemStatus::PENDING->value,
            'has-tags' => MetaItemStatus::PENDING->value,
            'has-assessments' => MetaItemStatus::PENDING->value,
            'has-topics' => MetaItemStatus::PENDING->value,
            'has-context' => MetaItemStatus::PENDING->value,
            'has-domains' => MetaItemStatus::PENDING->value,
            'has-jurisdictions' => MetaItemStatus::PENDING->value,
        ])
            ->assertSuccessful()
            ->assertDontSee('Related');

        $getRoute([
            'has-tags' => MetaItemStatus::COMPLETED->value,
            'has-actions' => MetaItemStatus::COMPLETED->value,
            'has-assessments' => MetaItemStatus::COMPLETED->value,
            'has-topics' => MetaItemStatus::COMPLETED->value,
            'has-context' => MetaItemStatus::COMPLETED->value,
            'has-domains' => MetaItemStatus::COMPLETED->value,
            'has-jurisdictions' => MetaItemStatus::COMPLETED->value,
        ])
            ->assertSuccessful()
            ->assertDontSee('Related');

        $getRoute([
            'has-actions' => MetaItemStatus::ANY->value,
            'has-tags' => MetaItemStatus::ANY->value,
            'has-assessments' => MetaItemStatus::ANY->value,
            'has-topics' => MetaItemStatus::ANY->value,
            'has-context' => MetaItemStatus::ANY->value,
            'has-domains' => MetaItemStatus::ANY->value,
            'has-jurisdictions' => MetaItemStatus::ANY->value,
        ])
            ->assertSuccessful()
            ->assertDontSee('Related');

        $getRoute([
            'has-actions',
            'has-tags',
            'has-assessments',
            'has-topics',
            'has-context',
            'has-domains',
            'has-jurisdictions',
        ])
            ->assertSuccessful()
            ->assertDontSee('Related');

        $response = $getRoute()
            ->assertSuccessful()
            ->assertDontSee('Related');

        $activate = $references->pop();

        $references->each(fn ($ref) => $response->assertSee($ref->refPlainText?->plain_text));

        $response = $getRoute(['activate' => '999999999'])
            ->assertSuccessful()
            ->assertDontSee('Related');

        $references->each(fn ($ref) => $response->assertSee($ref->refPlainText?->plain_text));

        $response = $getRoute(['activate' => $references->first()->id])
            ->assertSuccessful()
            ->assertSee('Related');

        $references->each(fn ($ref) => $response->assertSee($ref->refPlainText?->plain_text));

        $response = $getRoute(['activate' => $activate->id])->assertRedirect();

        $response = $this->turboGet($response->headers->get('Location'))
            ->assertSuccessful()
            ->assertSee('Related')
            ->assertSee($activate->refPlainText?->plain_text);

        $references->each(fn ($ref) => $response->assertDontSee($ref->refPlainText?->plain_text));

        /** @var Reference */
        $withMeta = Reference::factory()->create([
            'referenceable_id' => 1,
            'work_id' => $work->id,
            'type' => ReferenceType::citation(),
            'start' => $this->faker->unique()->numberBetween(1, 999999),
        ]);

        $meta = [
            'actionAreasWithDrafts' => [ActionArea::factory()->create()->id],
            'assessmentItemsWithDrafts' => [AssessmentItem::factory()->create()->id],
            'topicsWithDrafts' => [Category::factory()->create()->id],
            'questionsWithDrafts' => [ContextQuestion::factory()->create()->id],
            'tagsWithDrafts' => [Tag::factory()->create()->id],
            'locationsWithDrafts' => [Location::factory()->create()->id],
            'domainsWithDrafts' => [LegalDomain::factory()->create()->id],
        ];

        $withMeta->actionAreas()->attach($meta['actionAreasWithDrafts']);
        $withMeta->assessmentItems()->attach($meta['assessmentItemsWithDrafts']);
        $withMeta->categories()->attach($meta['topicsWithDrafts']);
        $withMeta->contextQuestions()->attach($meta['questionsWithDrafts']);
        $withMeta->tags()->attach($meta['tagsWithDrafts']);
        $withMeta->locations()->attach($meta['locationsWithDrafts']);
        $withMeta->legalDomains()->attach($meta['domainsWithDrafts']);

        session()->put(CollaborateSessionKey::CURRENT_VISIBLE_META->value, ['assessments' => 'assessmentItems']);
        $response = $getRoute($meta)
            ->assertSuccessful()
            ->assertSee($withMeta->title);

        $references->each(fn ($ref) => $response->assertDontSee($ref->refPlainText?->plain_text));

        // test multi filters without drafts
        $this->assertTrue(Reference::whereKey($withMeta->id)->filter(['actionAreas' => $meta['actionAreasWithDrafts']])->exists());
        $this->assertTrue(Reference::whereKey($withMeta->id)->filter(['assessmentItems' => $meta['assessmentItemsWithDrafts']])->exists());
        $this->assertTrue(Reference::whereKey($withMeta->id)->filter(['topics' => $meta['topicsWithDrafts']])->exists());
        $this->assertTrue(Reference::whereKey($withMeta->id)->filter(['questions' => $meta['questionsWithDrafts']])->exists());
        $this->assertTrue(Reference::whereKey($withMeta->id)->filter(['tags' => $meta['tagsWithDrafts']])->exists());
        $this->assertTrue(Reference::whereKey($withMeta->id)->filter(['locations' => $meta['locationsWithDrafts']])->exists());
        $this->assertTrue(Reference::whereKey($withMeta->id)->filter(['domains' => $meta['domainsWithDrafts']])->exists());
    }

    /**
     * @return void
     */
    public function testActivatesAndDeactivatesReferences(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $work = Work::factory()->create();
        $expression = WorkExpression::factory()->create(['work_id' => $work->id]);
        $reference = Reference::factory()->create(['referenceable_id' => 1, 'work_id' => $work->id, 'type' => ReferenceType::citation()]);
        $reference2 = Reference::factory()->create(['referenceable_id' => 1, 'work_id' => $work->id, 'type' => ReferenceType::citation()]);

        $this->setExpression($expression);

        $this->turboGet(route('collaborate.work-expressions.references.activate', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee('Related');
        $this->turboGet(route('collaborate.work-expressions.references.activate', ['expression' => $expression->id, 'reference' => $reference2->id]))
            ->assertSuccessful()
            ->assertSee('Related')
            ->assertSee($reference->refPlainText?->plain_text)
            ->assertSee($reference2->refPlainText?->plain_text);

        $this->turboGet(route('collaborate.work-expressions.references.deactivate', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertDontSee('Related');
    }

    public function testLoadingToc(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $work = Work::factory()->create();
        $expression = WorkExpression::factory()->create(['work_id' => $work->id]);
        $reference = Reference::factory()->create(['referenceable_id' => 1, 'work_id' => $work->id, 'type' => ReferenceType::citation()]);
        $reference2 = Reference::factory()->create(['referenceable_id' => 1, 'work_id' => $work->id, 'type' => ReferenceType::citation()]);

        $this->setExpression($expression);

        $this->turboGet(route('collaborate.work-expressions.references.toc', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($reference->title)
            ->assertSee($reference2->title);
    }
}
