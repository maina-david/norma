<?php

namespace Tests\Feature\Arachno;

use App\Models\Ontology\Tag;
use Tests\Feature\Abstracts\CrudTestCase;

class TagTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'title';

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Tag::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.tags';
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

    public function testJsonIndex(): void
    {
        $tag = Tag::factory()->create();

        $this->signIn();

        $route = route('collaborate.tags.index.json', ['search' => $tag->title]);

        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertJsonFragment([['id' => $tag->id, 'title' => $tag->title]]);
    }
}
