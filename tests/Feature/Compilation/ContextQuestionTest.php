<?php

namespace Tests\Feature\Compilation;

use App\Models\Compilation\ContextQuestion;
use Tests\Feature\Abstracts\CrudTestCase;

class ContextQuestionTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'prefix';

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return ContextQuestion::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.context-questions';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['predicate', 'pre_object'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return [
            'Object',
            'Post Object',
            'Pre Object',
            'Predicate',
            'Prefix',
        ];
    }

    /**
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
    {
        return ['Question'];
    }

    public function testActions(): void
    {
        $active = ContextQuestion::factory()->create();
        $inactive = ContextQuestion::factory()->create();

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->get(route('collaborate.context-questions.index'))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $payload = ["actions-checkbox-{$inactive->id}" => true];

        $this->followingRedirects()
            ->post(route('collaborate.context-questions.actions', ['action' => 'archive']), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $this->get(route('collaborate.context-questions.index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->question)
            ->assertSee($inactive->question);

        $this->get(route('collaborate.context-questions.index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertDontSee($inactive->question);

        $this->followingRedirects()
            ->post(route('collaborate.context-questions.actions', ['action' => 'restore']), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $this->get(route('collaborate.context-questions.index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->question)
            ->assertDontSee($inactive->question);

        $this->get(route('collaborate.context-questions.index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);
    }
}
