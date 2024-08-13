<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Enums\Workflows\MonitoringTaskFrequency;
use App\Enums\Workflows\TaskPriority;
use App\Enums\Workflows\TaskStartDay;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Workflows\MonitoringTaskRequest;
use App\Models\Workflows\MonitoringTaskConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MonitoringTaskController extends CollaborateController
{
    protected string $sortBy = 'title';

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return MonitoringTaskConfig::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.monitoring-task-configs';
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return MonitoringTaskRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => $row->title,
            'board' => fn ($row) => $row->board->title,
            'group' => fn ($row) => $row->group->title,
            'priority' => fn ($row) => TaskPriority::fromValue($row->priority)->label(),
            'start_day' => fn ($row) => TaskStartDay::tryFrom($row->start_day)?->label() ?? '',
            'frequency' => function ($row) {
                $label = MonitoringTaskFrequency::tryFrom($row->frequency)?->label() ?? '';
                $plural = $row->frequency_quantity > 1 ? 's' : '';

                return "{$row->frequency_quantity} {$label}{$plural}";
            },
            'enabled' => fn ($row) => $row->enabled ? __('interface.yes') : __('interface.no'),
            'last_run' => fn ($row) => $row->last_run?->format('d M Y') ?? '-',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['board', 'group'])
            ->when($request->routeIs('collaborate.monitoring-task-configs.show'), function ($builder) {
                $builder->with(['board.taskTypes']);
            });
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-5xl',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return $this->createViewData($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-3xl',
        ];
    }
}
