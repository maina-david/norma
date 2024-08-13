<?php

namespace Tests\Feature\Ontology;

use App\Models\Ontology\LegalDomain;
use Tests\Feature\Abstracts\CrudTestCase;

class LegalDomainTest extends CrudTestCase
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
        return LegalDomain::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.legal-domains';
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
        return ['Title'];
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
            'create', 'store', 'edit', 'update', 'destroy',
        ];
    }

    public function testActions(): void
    {
        $active = LegalDomain::factory()->create();
        $inactive = LegalDomain::factory()->create();

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->get(route('collaborate.legal-domains.index'))
            ->assertSuccessful()
            ->assertSee($active->title)
            ->assertSee($inactive->title);

        $payload = ["actions-checkbox-{$inactive->id}" => true];

        $this->followingRedirects()
            ->post(route('collaborate.legal-domains.actions', ['action' => 'archive']), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->title)
            ->assertSee($inactive->title);

        $this->get(route('collaborate.legal-domains.index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->title)
            ->assertSee($inactive->title);

        $this->get(route('collaborate.legal-domains.index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->title)
            ->assertDontSee($inactive->title);

        $this->followingRedirects()
            ->post(route('collaborate.legal-domains.actions', ['action' => 'restore']), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->title)
            ->assertSee($inactive->title);

        $this->get(route('collaborate.legal-domains.index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->title)
            ->assertDontSee($inactive->title);

        $this->get(route('collaborate.legal-domains.index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->title)
            ->assertSee($inactive->title);
    }
}
