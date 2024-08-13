<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use Tests\TestCase;

class ReferenceControllerTest extends TestCase
{
    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    public function testItRendersTheShowPage(): void
    {
        $reference = Reference::factory()->create();
        $routeName = 'collaborate.corpus.references.show';
        $route = route($routeName, ['reference' => $reference->id]);
        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($reference->title);
        $response = $this->get($route, ['turbo-frame' => true])->assertSuccessful();
        $response->assertSee($reference->title);
        // check nav items in subnav are visible
        $response->assertSee('Text');
        $response->assertSee('Legal Domains');
        $response->assertSee('Assessment Items');
        $response->assertSee('Topics');
        $response->assertSee('Context Questions');
        $response->assertSee('Jurisdictions');
        $response->assertSee('Tags');
    }
}
