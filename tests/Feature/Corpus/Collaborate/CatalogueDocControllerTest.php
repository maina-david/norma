<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Actions\Arachno\AddCatalogueDocsToFrontier;
use App\Http\ResourceActions\Corpus\FetchCatalogueDocs;
use App\Models\Arachno\Source;
use App\Models\Arachno\SourceCategory;
use App\Models\Corpus\CatalogueDoc;
use Mockery\MockInterface;
use Tests\Feature\Abstracts\CrudTestCase;

class CatalogueDocControllerTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'id';

    protected bool $descending = true;

    protected bool $searchable = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return CatalogueDoc::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.catalogue-docs';
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleLabels(): array
    {
        return [];
    }

    // /**
    //  * {@inheritDoc}
    //  */
    // protected static function visibleShowLabels(): array
    // {
    //     return ['ID', 'URL', 'Language'];
    // }

    /**
     * {@inheritDoc}
     */
    protected static function excludeActions(): array
    {
        return [
            'edit', 'update', 'destroy', 'show',
        ];
    }

    public function testIndexFiltersAndActions(): void
    {
        $docs = CatalogueDoc::factory(5)->create();
        $route = $this->getRoute('index');

        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        $routeName = 'collaborate.corpus.catalogue-docs.index';
        $route = route($routeName, ['search' => $docs[0]->title]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($docs[0]->title);
        $response->assertDontSee($docs[1]->title);

        /** @var Source */
        $source = Source::factory()->create();
        /** @var SourceCategory */
        $sourceCat = SourceCategory::factory()->create();
        $docs[0]->update(['source_id' => $source->id, 'fetch_started_at' => now()->subDays(2)]);
        $docs[0]->sourceCategories()->attach($sourceCat);
        $route = route($routeName, ['source' => $source->id, 'source_categories' => [$sourceCat->id]]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($docs[0]->title);
        $response->assertDontSee($docs[1]->title);

        $route = route($routeName, ['fetched' => 'true', 'linked' => 'true']);
        $this->get($route)->assertSuccessful();

        $route = route($routeName, ['failed-fetch' => 'true']);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($docs[0]->title);
        $response->assertDontSee($docs[1]->title);

        $this->partialMock(AddCatalogueDocsToFrontier::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle');
        });

        $payload = collect();
        $docs->each(function ($d) use ($payload) {
            /** @var CatalogueDoc $d */
            $payload->put("actions-checkbox-{$d->id}", true);
        });
        $payload = $payload->toArray();
        $payload['action'] = (new FetchCatalogueDocs())->actionId();

        $this->from(route('collaborate.corpus.catalogue-docs.index'))
            ->followingRedirects()
            ->post(route('collaborate.corpus.catalogue-docs.actions'), $payload)
            ->assertSuccessful();

        $this->get(route('collaborate.corpus.catalogue-docs.show', ['catalogueDoc' => $docs[0]->id]))
            ->assertRedirect(route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $docs[0]->id]));
    }
}
