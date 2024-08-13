<?php

namespace Tests\Feature\Workflows;

use App\Enums\Workflows\WizardStep;
use App\Http\ResourceActions\Workflows\ArchiveBoard;
use App\Http\ResourceActions\Workflows\DuplicateBoard;
use App\Http\ResourceActions\Workflows\RestoreBoard;
use App\Models\Workflows\Board;
use App\Models\Workflows\Pivots\BoardTaskType;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Feature\Abstracts\CrudTestCase;

class BoardControllerTest extends CrudTestCase
{
    protected string $sortBy = 'title';
    protected bool $collaborate = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Board::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.boards';
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleLabels(): array
    {
        return ['Title'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        if ($route === 'store') {
            return $factory->state([
                'wizard_steps' => WizardStep::CREATE_WORK->value,
                'task_types' => TaskType::factory()->count(2)->create()->pluck('id')->toArray(),
                'for_legal_update' => true,
            ]);
        }
        if ($route === 'update') {
            return $factory->state([
                'wizard_steps' => WizardStep::CREATE_WORK->value,
            ]);
        }

        return $factory;
    }

    /**
     * @return void
     */
    public function testCreatingDuplicates(): void
    {
        $taskTypes = TaskType::factory()->count(2)->create();

        $board = Board::factory()->create(['wizard_steps' => WizardStep::CREATE_WORK->value]);
        $board->taskTypes()->attach($taskTypes->pluck('id')->all());

        $this->signIn($this->collaborateSuperUser());

        $payload = [
            "actions-checkbox-{$board->id}" => true,
            'action' => (new DuplicateBoard())->actionId(),
        ];

        $this->assertDatabaseMissing(Board::class, ['title' => "{$board->title} - Copy"]);
        $this->assertSame(2, BoardTaskType::count());

        $this->post(route('collaborate.boards.actions'), $payload)->assertRedirect();

        $this->assertDatabaseHas(Board::class, ['title' => "{$board->title} - Copy"]);
        $this->assertSame(4, BoardTaskType::count());
    }

    public function testArchivingAndRestoring(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $board = Board::factory()->create();
        $payload = [
            "actions-checkbox-{$board->id}" => true,
            'action' => (new ArchiveBoard())->actionId(),
        ];

        $this->assertDatabaseHas(Board::class, ['id' => $board->id, 'archived_at' => null]);
        $this->post(route('collaborate.boards.actions'), $payload)->assertRedirect();
        $this->assertDatabaseMissing(Board::class, ['id' => $board->id, 'archived_at' => null]);

        $payload['action'] = (new RestoreBoard())->actionId();
        $this->post(route('collaborate.boards.actions'), $payload)->assertRedirect();
        $this->assertDatabaseHas(Board::class, ['id' => $board->id, 'archived_at' => null]);
    }
}
