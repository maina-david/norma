<?php

namespace Tests\Unit\Exports\Tasks;

use App\Enums\Tasks\TaskStatus;
use App\Exports\Tasks\TasksExcelExport;
use App\Models\Auth\User;
use App\Models\Notify\Reminder;
use App\Models\Tasks\Task;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class TasksExcelExportTest extends TestCase
{
    use CompilesStream;

    public function testBuild(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $tasks = Task::factory(5)->for($libryo)->create();
        $tasks->each(fn ($task) => $task->reminders()->create(Reminder::factory()->raw()));

        $progressCallback = function ($progress) {
        };

        $filters = [];
        $user = User::factory()->create();
        $excel = app(TasksExcelExport::class)->forLibryo($libryo, $organisation, $user, $filters, $progressCallback);

        $ws = $excel->getActiveSheet();
        $this->assertSame($tasks[0]->title, $ws->getCell('A2')->getValue());

        $user->libryos()->attach($libryo);

        $filters = ['statuses' => [TaskStatus::done()->value]];
        $tasks[1]->update(['task_status' => TaskStatus::done()->value]);
        $excel = app(TasksExcelExport::class)->forOrganisation($organisation, $user, $filters, $progressCallback);

        // only the filtered tasks should show
        $ws = $excel->getActiveSheet();
        $cell = $ws->getCell('A2');
        $this->assertSame($tasks[1]->title, $cell->getValue());
    }
}
