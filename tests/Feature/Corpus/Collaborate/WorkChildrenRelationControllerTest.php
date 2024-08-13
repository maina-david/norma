<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Http\ResourceActions\Corpus\DetachWorkFromWork;
use App\Models\Corpus\Pivots\WorkWork;
use App\Models\Corpus\Work;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use Illuminate\Support\Arr;
use Tests\Feature\Abstracts\CrudTestCase;

class WorkChildrenRelationControllerTest extends CrudTestCase
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
        return 'collaborate.works.children';
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
        $route = route('collaborate.works.children.index', $work);

        $frame = "children_corpus_work_{$work->id}";

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

        $route = route('collaborate.works.children.index', $work);

        $this->validateAuthGuard($route);

        $items = Work::factory()->count(10)->hasAttached($work, [], 'parents')
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

        $route = route('collaborate.works.children.create', $work);

        $this->validateAuthGuard($route);

        $this->collaboratorSignIn();

        $this->validateCollaborateRole($route);

        $this->get($route)->assertSuccessful()
            ->assertSee('Select the works that you would like to attach as children to ' . $work->title)
            ->assertSee('Selected works');
    }

    /**
     * @return void
     */
    public function testItSavesTheResource(): void
    {
        $work = Work::factory()->create();
        $child = Work::factory()->create();

        $route = route('collaborate.works.children.store', $work);

        $this->validateAuthGuard($route, 'post');

        $this->collaboratorSignIn();

        $this->validateCollaborateRole($route, 'post');

        $this->assertDatabaseMissing(WorkWork::class, ['parent_work_id' => $work->id, 'child_work_id' => $child->id]);

        $this->validateIndexItem($work, $child, false);

        $this->followingRedirects()
            ->post($route, ['works' => [$child->id]])
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'child_work_id' => $child->id]);

        $this->validateIndexItem($work, $child, true);
    }

    public function testDetachingWorks(): void
    {
        $work = Work::factory()->create();
        $child = Work::factory()->create();

        $work->children()->attach($child);

        $route = route('collaborate.works.children.actions', ['work' => $work, 'turbo-frame' => 'frame']);

        $payload["actions-checkbox-{$child->id}"] = true;

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'child_work_id' => $child->id]);

        $this->collaboratorSignIn();

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'child_work_id' => $child->id]);

        $this->validateCollaborateRole($route, 'post');

        $this->assertDatabaseHas(WorkWork::class, ['parent_work_id' => $work->id, 'child_work_id' => $child->id]);

        $payload['action'] = (new DetachWorkFromWork($work->id, 'children'))->actionId();

        $this->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing(WorkWork::class, ['parent_work_id' => $work->id, 'child_work_id' => $child->id]);
    }

    /**
     * @return void
     */
    public function testItRendersTheShowPage(): void
    {
        $work = Work::factory()->create();
        $child = Work::factory()->create();

        $work->children()->attach($child);

        $route = route('collaborate.works.children.show', ['work' => $work, 'child' => $child]);

        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $this->get($route)->assertRedirect(route('collaborate.works.show', $child));
    }
}
