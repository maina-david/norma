<?php

namespace Tests\Unit\Services\Actions;

use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Models\Actions\ActionArea;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Models\Tasks\Task;
use App\Services\Actions\DashboardStreamDataBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class DashboardStreamDataBuilderTest extends TestCase
{
    public function testBuildQuery(): void
    {
        $categories = Category::factory()->count(5)->create();
        /** @var Category */
        $parentCat = $categories->first();
        $childCategories = Category::factory()->count(5)->create(['parent_id' => $parentCat->id]);
        $action = ActionArea::factory()->create(['control_category_id' => $parentCat->id, 'subject_category_id' => $parentCat->id]);

        /** @var Organisation */
        $organisation = Organisation::factory()->create();
        /** @var Libryo */
        $libryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);
        /** @var User */
        $user = User::factory()->create();
        /** @var Collection<Task> */
        $tasks = Task::factory(3)->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id, 'action_area_id' => $action->id]);
        $service = app(DashboardStreamDataBuilder::class);
        $builder = $service->buildQuery(
            $organisation->id,
            [
                'total_tasks',
                'total_in_progress_tasks',
                'total_not_started_tasks',
                'overdue_tasks',
                'completed_total_impact',
                'incomplete_total_impact',
            ],
            [
                'task_statuses' => [TaskStatus::notStarted()->value],
                'task_assignee' => $user->id,
                'task_max_impact' => '10',
                'task_min_impact' => '1',
                'task_type' => 'generic',
                'task_priority' => TaskPriority::low()->value,
                'task_start_date' => now()->subDays(3)->format('Y-m-d'),
                'task_end_date' => now()->addMonths(12)->format('Y-m-d'),
                'streams' => [$libryo->id],
                'task_control_types' => [$parentCat->id],
                'task_topics' => [$parentCat->id],
            ]
        );

        $this->assertInstanceOf(Builder::class, $builder);
    }
}
