<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class ForWorkReferenceControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    public function testItRendersTheIndexPage(): void
    {
        $work = Work::factory()->create();
        $refs = Reference::factory(5)->for($work)->create();
        $routeName = 'collaborate.corpus.works.references.index';
        $route = route($routeName, ['work' => $work->id]);
        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);
        $route = route($routeName, ['work' => $work->id, 'search' => $refs[0]->title]);
        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($refs[0], $response);
    }
}
