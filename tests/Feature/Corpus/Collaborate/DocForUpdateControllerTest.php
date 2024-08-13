<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Arachno\Source;
use App\Models\Corpus\Doc;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Feature\Abstracts\CrudTestCase;

class DocForUpdateControllerTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'id';

    protected bool $descending = true;

    protected bool $searchable = false;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Doc::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.docs.for-update';
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

    /**
     * {@inheritDoc}
     */
    protected static function visibleShowLabels(): array
    {
        return ['ID', 'URL', 'Language'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function excludeActions(): array
    {
        return [
            'create', 'store', 'edit', 'update', 'destroy',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        return $factory->state(['for_update' => true]);
    }

    public function testIndexFilters(): void
    {
        $docs = Doc::factory(5)->create(['for_update' => true]);
        $route = $this->getRoute('index');

        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        $routeName = 'collaborate.corpus.docs.for-update.index';
        $route = route($routeName, ['search' => $docs[0]->title]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($docs[0]->title);
        $response->assertDontSee($docs[1]->title);

        $domain = LegalDomain::factory()->create();
        $docs[0]->legalDomains()->attach($domain);
        $route = route($routeName, ['domains' => [$domain->id]]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($docs[0]->title);
        // assert see the legal domain badges in the title column
        $response->assertSeeSelector('//tr//*[text()[contains(.,"' . $domain->title . '")]]');
        $response->assertDontSee($docs[1]->title);

        $location = Location::factory()->create();
        $source = Source::factory()->create();
        $docs[0]->update(['primary_location_id' => $location->id, 'source_id' => $source->id]);
        $route = route($routeName, ['jurisdiction' => $location]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($docs[0]->title);
        $response->assertDontSee($docs[1]->title);

        $route = route($routeName, ['source' => $source]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($docs[0]->title);
        $response->assertDontSee($docs[1]->title);
    }

    public function testCreateUpdateFromDoc(): void
    {
        $doc = Doc::factory()->create(['for_update' => true]);
        $route = route('collaborate.corpus.docs.for-update.create-update', ['doc' => $doc->id]);

        $this->validateAuthGuard($route, 'post');
        $this->signIn($this->collaborateSuperUser());
        $this->post($route)->assertRedirect();
    }
}
