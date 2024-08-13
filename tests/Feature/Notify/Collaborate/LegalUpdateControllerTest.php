<?php

namespace Tests\Feature\Notify\Collaborate;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Models\Arachno\Source;
use App\Models\Collaborators\Collaborator;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\LegalDomain;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Abstracts\CrudTestCase;

class LegalUpdateControllerTest extends CrudTestCase
{
    /** @var bool */
    protected bool $searchable = true;

    /** @var string */
    protected string $sortBy = 'id';

    protected bool $descending = true;

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return LegalUpdate::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.legal-updates';
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
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        return in_array($route, ['store', 'update'])
            ? $factory->state(function () {
                $faker = Container::getInstance()->make(Generator::class);

                return [
                    'domains' => [LegalDomain::factory()->create()->id],
                    'content_resource_file' => UploadedFile::fake()->createWithContent('test.html', $faker->randomHtml()),
                ];
            })
            : $factory;
    }

    /**
     * @return void
     */
    public function testItFiltersCorrectly(): void
    {
        $source = Source::factory()->create();
        $updates = LegalUpdate::factory()
            ->count(4)
            ->create(['source_id' => $source->id, 'status' => LegalUpdatePublishedStatus::PUBLISHED->value]);

        $first = $updates->shift();

        $this->signIn($this->collaborateSuperUser());

        $routeName = 'collaborate.legal-updates.index';

        $response = $this->get(route($routeName))
            ->assertSee($first->title);

        $updates->each(fn ($update) => $response->assertSee($update->title));

        $domain = LegalDomain::factory()->create();
        $first->legalDomains()->attach($domain->id);

        $response = $this->get(route($routeName, ['domains' => [$domain->id]]))
            ->assertSee($first->title);
        $updates->each(fn ($update) => $response->assertDontSee($update->title));

        $withoutSource = $updates->first();
        $withoutSource->update(['source_id' => null]);
        $response = $this->get(route($routeName, ['source' => $source->id]))
            ->assertSee($first->title);
        $response->assertDontSee($withoutSource->title);

        $location = Location::factory()->create();
        $ancestor = Location::factory()->create();
        $location->ancestors()->attach($ancestor, ['depth' => 1]);
        $first->update(['primary_location_id' => $location->id]);
        $response = $this->get(route($routeName, ['jurisdiction' => $ancestor->id]))
            ->assertSee($first->title);
        $updates->each(fn ($update) => $response->assertDontSee($update->title));

        $response = $this->get(route($routeName, ['published' => 'no']));
        $updates->each(fn ($update) => $response->assertDontSee($update->title));
    }

    public function testPublishing(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());

        $update = LegalUpdate::factory()->create(['status' => LegalUpdatePublishedStatus::UNPUBLISHED->value, 'release_at' => null, 'highlights' => null]);

        $this->put(route('collaborate.legal-updates.publish', ['legal_update' => $update->id]))
            ->assertSessionHas('flash.type', 'error');

        $this->put(route('collaborate.legal-updates.not-applicable', ['legal_update' => $update->id]))
            ->assertSessionHas('flash.type', 'error');

        $update->update([
            'release_at' => now(),
            'highlights' => $this->faker->paragraph,
            'update_report' => $this->faker->paragraph,
        ]);

        $this->put(route('collaborate.legal-updates.publish', ['legal_update' => $update->id]))
            ->assertSessionMissing('flash.type');

        $this->assertSame(LegalUpdatePublishedStatus::PUBLISHED->value, $update->refresh()->status);

        $this->put(route('collaborate.legal-updates.not-applicable', ['legal_update' => $update->id]))
            ->assertSessionMissing('flash.type');

        $this->assertSame(LegalUpdatePublishedStatus::NOT_APPLICABLE->value, $update->refresh()->status);
    }

    /**
     * @return void
     */
    public function testItUpdatesTheResource(): void
    {
        $resource = static::resource();

        $item = $this->preCreate($resource::factory(), 'update')->raw();

        $original = LegalUpdate::factory()->create();

        $route = $this->getRoute('update', $original->toArray());

        $this->validateAuthGuard($route, 'put');

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        $this->validateCollaborateRole($route, 'put');

        $indexRoute = $this->redirectAfterUpdate($original->replicate()->fill($item));

        $response = $this->get($indexRoute)->assertSuccessful();

        $this->assertDontSeeVisibleFields($item, $response);
        $this->assertSeeVisibleFields($original, $response);

        $this->followingRedirects()->put($route, $item)->assertSuccessful();

        $response = $this->get($indexRoute)->assertSuccessful();

        $this->assertDontSeeVisibleFields($original, $response);
        $this->assertSeeVisibleFields($item, $response);
    }
}
