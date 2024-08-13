<?php

namespace Tests\Feature\Abstracts;

use App\Models\Collaborators\Collaborator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

abstract class CrudTestCase extends TestCase
{
    use HasVisibleFieldAssertions;

    protected string $sortBy = 'id';

    protected bool $descending = false;

    protected bool $searchable = true;

    protected bool $collaborate = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    abstract protected static function resource(): string;

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    abstract protected static function resourceRoute(): string;

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    abstract protected static function visibleFields(): array;

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    abstract protected static function visibleLabels(): array;

    /**
     * Get payload for the base resource route.
     *
     * @param array $params
     *
     * @return array<string, mixed>
     */
    protected static function resourceRouteParams(array $params): array
    {
        return [];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array<string>
     */
    protected static function excludeActions(): array
    {
        return [];
    }

    /**
     * Perform tasks before creation of the test model.
     *
     * @param Factory $factory
     * @param string  $route
     *
     * @return Factory
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        return $factory;
    }

    /**
     * Get the route to be used.
     *
     * @param string       $action
     * @param array<mixed> $payload
     *
     * @return string
     */
    protected function getRoute(string $action, array $payload = []): string
    {
        $params = [];

        if (!in_array($action, ['store', 'index']) && isset($payload['id'])) {
            $params[] = $payload['id'];
        }

        $params = array_merge($params, static::resourceRouteParams($payload));

        return route(sprintf('%s.%s', static::resourceRoute(), $action), $params);
    }

    /**
     * @return void
     */
    public function testItRendersTheIndexPage(): void
    {
        if (in_array('index', static::excludeActions())) {
            $this->assertTrue(true);

            return;
        }

        $route = $this->getRoute('index');

        $this->validateAuthGuard($route);

        $resource = static::resource();

        $items = $this->preCreate($resource::factory(15), 'index')
            ->create()
            ->sortBy($this->sortBy, descending: $this->descending)
            ->values();

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        if (Str::of(static::resourceRoute())->startsWith('admin.')) {
            $this->validateAdminRole($route);
        } else {
            $this->validateCollaborateRole($route);
        }

        $response = $this->get($route)->assertSuccessful();

        $search = $this->assertSeeVisibleFields($items[0], $response);

        if ($this->searchable && $search) {
            $query = "search={$search}";
            $route = str_contains($route, '?') ? "{$route}&{$query}" : "{$route}?{$query}";
            $this->get($route)
                ->assertSuccessful()
                ->assertSee($search);
        }
    }

    /**
     * @return void
     */
    public function testItRendersTheCreatePage(): void
    {
        if (in_array('create', static::excludeActions())) {
            $this->assertTrue(true);

            return;
        }

        $resource = static::resource();
        $this->preCreate($resource::factory(), 'create');

        $route = $this->getRoute('create');

        $this->validateAuthGuard($route);

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        if (Str::of(static::resourceRoute())->startsWith('admin.')) {
            $this->validateAdminRole($route);
        } else {
            $this->validateCollaborateRole($route);
        }

        $response = $this->get($route)->assertSuccessful();

        foreach (static::visibleLabels() as $label) {
            $response->assertSee($label);
        }
    }

    /**
     * @return void
     */
    public function testItSavesTheResource(): void
    {
        if (in_array('store', static::excludeActions())) {
            $this->assertTrue(true);

            return;
        }

        $this->from(in_array('create', static::excludeActions()) ? '/' : $this->getRoute('create'));

        $resource = static::resource();

        $item = $this->preCreate($resource::factory(), 'store')->raw();

        $route = $this->getRoute('store', $item);

        $this->validateAuthGuard($route, 'post');

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        if (Str::of(static::resourceRoute())->startsWith('admin.')) {
            $this->validateAdminRole($route, 'post');
        } else {
            $this->validateCollaborateRole($route, 'post');
        }

        $response = $this->get($this->redirectAfterStore($item))->assertSuccessful();

        $this->assertDontSeeVisibleFields($item, $response);

        $this->followingRedirects()
            ->post($route, $item)
            ->assertSuccessful();

        $response = $this->get($this->redirectAfterStore($item))->assertSuccessful();

        $this->assertSeeVisibleFields($item, $response);
    }

    /**
     * @return void
     */
    public function testItRendersTheEditPage(): void
    {
        if (in_array('edit', static::excludeActions())) {
            $this->assertTrue(true);

            return;
        }

        $resource = static::resource();

        $item = $this->preCreate($resource::factory(), 'edit')->create();

        $route = $this->getRoute('edit', $item->toArray());

        $this->validateAuthGuard($route);

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        if (Str::of(static::resourceRoute())->startsWith('admin.')) {
            $this->validateAdminRole($route);
        } else {
            $this->validateCollaborateRole($route);
        }

        $response = $this->get($route)->assertSuccessful();

        foreach (static::visibleLabels() as $label) {
            $response->assertSee($label);
        }

        $this->assertSeeVisibleFields($item, $response);
    }

    /**
     * @return void
     */
    public function testItRendersTheShowPage(): void
    {
        if (in_array('show', static::excludeActions())) {
            $this->assertTrue(true);

            return;
        }

        $resource = static::resource();

        $item = $this->preCreate($resource::factory(), 'show')->create();

        $route = $this->getRoute('show', $item->toArray());

        $this->validateAuthGuard($route);

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        if (Str::of(static::resourceRoute())->startsWith('admin.')) {
            $this->validateAdminRole($route);
        } else {
            $this->validateCollaborateRole($route);
        }

        $response = $this->get($route)->assertSuccessful();

        $labels = method_exists(static::class, 'visibleShowLabels')
            ? static::visibleShowLabels()
            : static::visibleLabels();

        foreach ($labels as $label) {
            $response->assertSee($label);
        }

        $this->assertSeeVisibleFields($item, $response);
    }

    /**
     * @return void
     */
    public function testItUpdatesTheResource(): void
    {
        if (in_array('update', static::excludeActions())) {
            $this->assertTrue(true);

            return;
        }

        $resource = static::resource();

        $original = $this->preCreate($resource::factory(), 'update')->create();

        $item = $this->preCreate($resource::factory(), 'update')->raw();

        $route = $this->getRoute('update', $original->toArray());

        $this->validateAuthGuard($route, 'put');

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        if (Str::of(static::resourceRoute())->startsWith('admin.')) {
            $this->validateAdminRole($route, 'put');
        } else {
            $this->validateCollaborateRole($route, 'put');
        }

        $indexRoute = $this->redirectAfterUpdate($original->replicate()->fill($item));

        $response = $this->get($indexRoute)->assertSuccessful();

        $this->assertDontSeeVisibleFields($item, $response);
        $this->assertSeeVisibleFields($original, $response);

        $this->followingRedirects()->put($route, $item)->assertSuccessful();

        $response = $this->get($indexRoute)->assertSuccessful();

        $this->assertDontSeeVisibleFields($original, $response);
        $this->assertSeeVisibleFields($item, $response);
    }

    /**
     * @return void
     */
    public function testItDeletesTheResource(): void
    {
        if (in_array('destroy', static::excludeActions())) {
            $this->assertTrue(true);

            return;
        }

        $resource = static::resource();

        $item = $this->preCreate($resource::factory(), 'destroy')->create();

        $route = $this->getRoute('destroy', $item->toArray());

        $this->validateAuthGuard($route, 'delete');

        $user = $this->collaborate ? Collaborator::factory()->create() : null;

        $this->signIn($user);

        if (Str::of(static::resourceRoute())->startsWith('admin.')) {
            $this->validateAdminRole($route, 'delete');
        } else {
            $this->validateCollaborateRole($route, 'delete');
        }

        $item = $this->preCreate($resource::factory(), 'destroy')->create();

        $route = $this->getRoute('destroy', $item->toArray());

        $indexRoute = $this->redirectAfterDestroy($item);

        $response = $this->get($indexRoute)->assertSuccessful();

        $this->assertSeeVisibleFields($item, $response);

        $this->followingRedirects()->delete($route)->assertSuccessful();

        $response = $this->get($indexRoute)->assertSuccessful();

        $this->assertDontSeeVisibleFields($item, $response);
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param array $model
     *
     * @return string
     */
    protected function redirectAfterStore(array $model): string
    {
        return $this->getRoute('index', $model);
    }

    /**
     * Which route to redirect to after destroy action.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function redirectAfterDestroy(Model $model): string
    {
        return $this->redirectAfterStore($model->toArray());
    }

    /**
     * Which route to redirect to after update action.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        return $this->redirectAfterStore($model->toArray());
    }
}
