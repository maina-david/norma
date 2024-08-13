<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Actions\Corpus\CatalogueWork\SyncToWork;
use App\Http\ResourceActions\Corpus\SyncCatalogueToWork;
use App\Models\Arachno\Source;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Corpus\CatalogueWork;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Abstracts\CrudTestCase;

class CatalogueWorkControllerTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'title';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return CatalogueWork::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.catalogue-works';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return [];
    }

    /**
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
    {
        return [];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array
     */
    protected static function excludeActions(): array
    {
        return [
            'create', 'store', 'edit', 'update', 'show',
        ];
    }

    public function testIndexFilters(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $routeName = 'collaborate.catalogue-works.index';
        $source = Source::factory()->create();
        $reqCollection = RequirementsCollection::factory()->create();
        $catalogueWork = CatalogueWork::factory()->create(['source_id' => $source->id, 'primary_location_id' => $reqCollection->id]);
        $catalogueWork2 = CatalogueWork::factory()->create();
        $this->get(route($routeName, ['search' => $catalogueWork->title]))
            ->assertSuccessful()
            ->assertSee($catalogueWork->title)
            ->assertDontSee($catalogueWork2->title);
        $this->get(route($routeName, ['source' => $source->id]))
            ->assertSuccessful()
            ->assertSee($catalogueWork->title)
            ->assertDontSee($catalogueWork2->title);
        $this->get(route($routeName, ['jurisdiction' => $reqCollection->id]))
            ->assertSuccessful()
            ->assertSee($catalogueWork->title)
            ->assertDontSee($catalogueWork2->title);
    }

    public function testCatalogueWorkSync(): void
    {
        Queue::fake();
        $cWorks = CatalogueWork::factory()->count(2)->create();
        $route = route('collaborate.catalogue-works.actions');

        $payload = collect();

        $cWorks->each(function ($w) use ($payload) {
            $payload->put("actions-checkbox-{$w->id}", true);
        });

        $this->validateAuthGuard($route, 'post');
        $this->signIn();
        $this->from(route('collaborate.catalogue-works.index'));
        $this->validateCollaborateRole($route, 'post');

        $payload = $payload->toArray();
        $payload['action'] = (new SyncCatalogueToWork())->actionId();

        $this->from(route('collaborate.catalogue-works.index'))
            ->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful();

        SyncToWork::assertPushed();
    }
}
