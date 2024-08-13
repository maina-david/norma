<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Http\ResourceActions\Corpus\DetachWorkFromWork;
use App\Models\Corpus\Pivots\WorkWork;
use App\Models\Corpus\Work;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use Illuminate\Support\Arr;
use Tests\Feature\Abstracts\CrudTestCase;

class WorkParentsRelationControllerTest extends CrudTestCase
{
    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Work::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.works.parents';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['start_date'];
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
            'edit', 'update', 'destroy',
        ];
    }

    protected function validateIndexItem(Work $work, $item, bool $visible): void
    {
        $route = route('collaborate.works.parents.index', $work);

        $frame = "parents_corpus_work_{$work->id}";

        $response = $this->get($route, ['turbo-frame' => $frame])->assertSuccessful();

        if (!$visible) {
            $response->assertDontSee($item, 'title');
        }

        $response->assertTurboStream(function (AssertableTurboStream $streams) use ($visible, $item, $frame) {
            return $streams->has(1)->hasTurboStream(function ($stream) use ($visible, $item, $frame) {
                $stream->where('target', $frame)->where('action', 'update');

                if ($visible) {
                    $stream->see(Arr::get($item, 'title'));
                }

                return $stream;
            });
        });
    }

    /**
     * @return void
     */
    public function testItRendersTheIndexPage(): void
    {
        $work = Work::factory()->create();

        $route = route('collaborate.works.parents.index', $work);

        $this->validateAuthGuard($route);

        $items = Work::factory()->count(10)->hasAttached($work, [], 'children')
            ->create()
            ->sortBy($this->sortBy, descending: $this->descending)
            ->values();

        $this->collaboratorSignIn();

        $this->validateCollaborateRole($route);

        $this->validateIndexItem($work, $items[0], true);
    }

    /**
     * @return void
     */
    public function testItRendersTheCreatePage(): void
    {
        $work = Work::factory()->create();

        $route = route('collaborate.works.parents.create', $work);

        $this->validateAuthGuard($route);

        $this->collaboratorSignIn();

        $this->validateCollaborateRole($route);

        $this->get($route)->assertSuccessful()
            ->assertSee('Select the works that you would like to attach as parents to ' . $work->title)
            ->assertSee('Selected works');
    }

    /**
     * @return void
     */
    public function testItSavesTheResource(): void
    {
        $work = Work::factory()->create();
        $parent = Work::factory()->create();

        $route = route('collaborate.works.parents.store', $work);

        $this->validateAuthGuard($route, 'post');

        $this->collaboratorSignIn();

        $this->validateCollaborateRole($route, 'post');

        $this->assertDatabaseMissing(WorkWork::class, ['parent_work_id' => $work->id, 'parent_work_id' => $parent->id]);

        $this->validateIndexItem($work, $parent, false);

        $this->followingRedirects()
            ->post($route, ['works' => [$parent->id]])
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'parent_work_id' => $parent->id]);

        $this->validateIndexItem($work, $parent, true);
    }

    public function testDetachingWorks(): void
    {
        $work = Work::factory()->create();
        $parent = Work::factory()->create();

        $work->parents()->attach($parent);

        $route = route('collaborate.works.parents.actions', ['work' => $work, 'turbo-frame' => 'frame']);

        $payload["actions-checkbox-{$parent->id}"] = true;

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'parent_work_id' => $parent->id]);

        $this->collaboratorSignIn();

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'parent_work_id' => $parent->id]);

        $this->validateCollaborateRole($route, 'post');

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'parent_work_id' => $parent->id]);

        $payload['action'] = (new DetachWorkFromWork($work->id, 'parents'))->actionId();

        $this->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing(WorkWork::class, ['parent_work_id' => $work->id, 'parent_work_id' => $parent->id]);
    }

    /**
     * @return void
     */
    public function testItRendersTheShowPage(): void
    {
        $work = Work::factory()->create();
        $parent = Work::factory()->create();

        $work->parents()->attach($parent);

        $route = route('collaborate.works.parents.show', ['work' => $work, 'parent' => $parent]);

        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $this->get($route)->assertRedirect(route('collaborate.works.show', $parent));
    }
}
