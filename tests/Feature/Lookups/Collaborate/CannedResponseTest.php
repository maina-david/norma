<?php

namespace Tests\Feature\Lookups\Collaborate;

use App\Models\Lookups\CannedResponse;
use Tests\Feature\Abstracts\CrudTestCase;

class CannedResponseTest extends CrudTestCase
{
    protected bool $collaborate = true;

    /** @var string */
    protected string $sortBy = 'response';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return CannedResponse::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.canned-responses';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['response'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Response', 'For Field'];
    }

    public function testItFiltersCorrectly(): void
    {
        $responses = CannedResponse::factory()
            ->count(4)
            ->create();

        $first = $responses->shift();

        $this->signIn($this->collaborateSuperUser());

        $response = $this->get(route('collaborate.canned-responses.index'))
            ->assertSee($first->response);

        $responses->each(function ($r) use ($response) {
            $response->assertSee($r->response);
            $r->update(['for_field' => 'something_else']);
        });

        $response = $this->get(route('collaborate.canned-responses.index', ['for_field' => $first->for_field]))
            ->assertSee($first->response);

        $responses->each(fn ($r) => $response->assertDontSee($r->response));
    }
}
