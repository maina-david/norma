<?php

namespace Tests\Unit\Exports\Actions;

use App\Enums\Tasks\TaskStatus;
use App\Exports\Actions\DashboardStreamDataExport;
use App\Models\Tasks\Task;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class DashboardStreamDataExportTest extends TestCase
{
    use CompilesStream;

    public function testBuild(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $taskCount = 5;
        $tasks = Task::factory($taskCount)->for($libryo)->create();

        $progressCallback = function ($progress) {
        };

        $columns = [
            'title',
            'total_tasks',
            'total_in_progress_tasks',
            'total_not_started_tasks',
            'overdue_tasks',
            'completed_total_impact',
            'incomplete_total_impact',
        ];
        $filters = [];
        $excel = app(DashboardStreamDataExport::class)->forOrganisation($organisation, $columns, $filters, $progressCallback);

        $ws = $excel->getActiveSheet();
        $this->assertSame($libryo->title, $ws->getCell('A2')->getValue());
        $this->assertSame($taskCount, (int) $ws->getCell('B2')->getValue());

        $filters = ['task_statuses' => [TaskStatus::done()->value]];
        $tasks[1]->update(['task_status' => TaskStatus::done()->value]);
        $excel = app(DashboardStreamDataExport::class)->forOrganisation($organisation, $columns, $filters, $progressCallback);

        // only the filtered tasks should be counted
        $ws = $excel->getActiveSheet();
        $this->assertSame(1, (int) $ws->getCell('B2')->getValue());
    }
}
