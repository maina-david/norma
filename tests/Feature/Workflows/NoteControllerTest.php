<?php

namespace Tests\Feature\Workflows;

use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Note;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    public function testManagementOfNotes(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create();

        $this->turboPost(route('collaborate.notes.store', ['work' => $expression->work_id]), ['note' => 'Something cool!'])
            ->assertRedirect();
        $this->turboPost(route('collaborate.notes.store', ['work' => $expression->work_id]), ['note' => 'Cool something!'])
            ->assertRedirect();

        $this->turboGet(route('collaborate.notes.index', ['work' => $expression->work_id]))
            ->assertSuccessful()
            ->assertSee('Something cool!')
            ->assertSee('Cool something!');

        $this->turboGet(route('collaborate.notes.index', ['work' => $expression->work_id, 'expression' => $expression->id]))
            ->assertSuccessful()
            ->assertDontSee('Something cool!')
            ->assertDontSee('Cool something!');

        $this->turboPost(route('collaborate.notes.store', ['work' => $expression->work_id, 'expression' => $expression->id]), ['note' => 'Something expressing coolness!'])
            ->assertRedirect();
        $this->turboPost(route('collaborate.notes.store', ['work' => $expression->work_id, 'expression' => $expression->id]), ['note' => 'Coolness expressed by something!'])
            ->assertRedirect();

        $this->turboGet(route('collaborate.notes.index', ['work' => $expression->work_id]))
            ->assertSuccessful()
            ->assertSee('Something cool!')
            ->assertDontSee('Something expressing coolness!')
            ->assertDontSee('Coolness expressed by something!');

        $this->turboGet(route('collaborate.notes.index', ['work' => $expression->work_id, 'expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee('Something expressing coolness!')
            ->assertSee('Coolness expressed by something!')
            ->assertDontSee('Something cool!');

        /** @var Note $note */
        $note = Note::where('notable_type', (new Work())->getMorphClass())->first();

        $this->turboGet(route('collaborate.notes.edit', ['note' => $note->id]))
            ->assertSuccessful()
            ->assertSee($note->content)
            ->assertDontSee('Cool something!');

        $this->turboPut(route('collaborate.notes.update', ['note' => $note->id]), ['note' => 'Something extra extra cool!'])
            ->assertRedirect();

        $this->turboGet(route('collaborate.notes.index', ['work' => $expression->work_id]))
            ->assertSuccessful()
            ->assertSee('Something extra extra cool!')
            ->assertDontSee($note->content)
            ->assertDontSee('Coolness expressed by something!');

        $note = Note::where('notable_type', (new WorkExpression())->getMorphClass())->first();

        $this->turboDelete(route('collaborate.notes.update', ['note' => $note->id]))
            ->assertRedirect();

        $this->turboGet(route('collaborate.notes.index', ['work' => $expression->work_id, 'expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee('Coolness expressed by something!')
            ->assertDontSee('Something expressing coolness!')
            ->assertDontSee('Something cool!');
    }
}
