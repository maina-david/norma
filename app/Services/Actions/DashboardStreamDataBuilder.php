<?php

namespace App\Services\Actions;

use App\Enums\Tasks\TaskStatus;
use App\Enums\Tasks\TaskType;
use App\Models\Actions\ActionArea;
use App\Models\Customer\Norma;
use App\Models\Ontology\Pivots\CategoryClosure;
use App\Models\Tasks\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DashboardStreamDataBuilder
{
    /**
     * @param int                  $organisationId
     * @param array<string>        $columns
     * @param array<string, mixed> $filtered
     *
     * @return Builder
     */
    public function buildQuery(int $organisationId, array $columns = [], array $filtered = []): Builder
    {
        $normaTable = (new Norma())->getTable();
        $taskTable = (new Task())->getTable();
        $selects = [$normaTable . '.id', $normaTable . '.title'];
        $availableSelects = $this->getColumnSelects();

        // if columns is empty, just do all columns
        $columns = empty($columns) ? array_keys($availableSelects) : $columns;
        foreach ($columns as $column) {
            if (isset($availableSelects[$column])) {
                $selects[] = $availableSelects[$column];
            }
        }

        $query = Norma::forOrganisation($organisationId)
            ->leftJoin($taskTable, function ($join) use ($taskTable, $normaTable, $filtered) {
                $join->on($taskTable . '.place_id', $normaTable . '.id');

                if (!empty($filtered)) {
                    $this->filterTasks($join, $filtered);
                }
            })
            ->select($selects)
            ->groupBy((new Norma())->getTable() . '.id');

        if (!empty($filtered)) {
            $this->filterStreams($query, $filtered);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getColumnSelects(): array
    {
        $taskTable = (new Task())->getTable();
        $endOfDay = now()->yesterday()->endOfDay();
        $notStarted = TaskStatus::notStarted()->value;
        $inProgress = TaskStatus::inProgress()->value;
        $done = TaskStatus::done()->value;

        return [
            'total_tasks' => DB::raw('count(DISTINCT ' . $taskTable . '.id) as total_tasks'),
            'total_in_progress_tasks' => DB::raw("SUM(CASE WHEN {$taskTable}.task_status = {$inProgress} THEN 1 ELSE 0 END) AS total_in_progress_tasks"),
            'total_not_started_tasks' => DB::raw("SUM(CASE WHEN {$taskTable}.task_status = {$notStarted} THEN 1 ELSE 0 END) AS total_not_started_tasks"),
            'overdue_tasks' => DB::raw("SUM(CASE WHEN ({$taskTable}.task_status = {$notStarted} or {$taskTable}.task_status = {$inProgress}) AND {$taskTable}.due_on < '{$endOfDay}' THEN 1 ELSE 0 END) AS overdue_tasks"),
            'completed_total_impact' => DB::raw("SUM(CASE WHEN {$taskTable}.task_status = {$done} THEN {$taskTable}.impact ELSE 0 END) AS completed_total_impact"),
            'incomplete_total_impact' => DB::raw("SUM(CASE WHEN ({$taskTable}.task_status = {$notStarted} or {$taskTable}.task_status = {$inProgress}) THEN {$taskTable}.impact ELSE 0 END) AS incomplete_total_impact"),
        ];
    }

    /**
     * @param JoinClause           $join
     * @param array<string, mixed> $filtered
     *
     * @return void
     */
    protected function filterTasks(JoinClause $join, array $filtered = [])
    {
        $availableTaskFilters = [
            'task_statuses',
            'task_priority',
            'task_assignee',
            'task_priority',
            'task_max_impact',
            'task_min_impact',
            'task_type',
            'task_start_date',
            'task_end_date',
            'task_control_types',
            'task_topics',
        ];
        $taskFilters = [];

        foreach ($filtered as $key => $filterValue) {
            if (!in_array($key, $availableTaskFilters) || is_null($filterValue)) {
                continue;
            }
            $taskFilters[Str::remove('task_', $key)] = $filterValue;
        }

        foreach ($taskFilters as $key => $value) {
            $this->filterTask($join, $key, $value);
        }
    }

    /**
     * @param JoinClause $join
     * @param mixed      $value
     *
     * @return void
     */
    protected function filterTask(JoinClause $join, string $key, $value)
    {
        switch ($key) {
            case 'statuses':
                $join->whereIn('task_status', $value);
                break;
            case 'control_types':
                $join->where(function ($q) use ($value) {
                    $q->whereExists(function ($q) use ($value) {
                        $actionAreaTable = (new ActionArea())->getTable();
                        $q->select(DB::raw(1))
                            ->from($actionAreaTable)
                            ->whereRaw($actionAreaTable . '.id = action_area_id')
                            ->whereIn($actionAreaTable . '.control_category_id', $value);
                    })
                        ->orWhereExists(function ($q) use ($value) {
                            $actionAreaTable = (new ActionArea())->getTable();
                            $q->select(DB::raw(1))
                                ->from($actionAreaTable)
                                ->whereRaw($actionAreaTable . '.id = action_area_id')
                                ->whereExists(function ($q) use ($value) {
                                    $closureTable = (new CategoryClosure())->getTable();
                                    $q->select(DB::raw(1))
                                        ->from($closureTable)
                                        ->where($closureTable . '.depth', '!=', 0)
                                        ->whereRaw($closureTable . '.descendant = control_category_id')
                                        ->whereIn($closureTable . '.ancestor', $value);
                                });
                        });
                });
                break;
            case 'topics':
                $join->where(function ($q) use ($value) {
                    $q->whereExists(function ($q) use ($value) {
                        $actionAreaTable = (new ActionArea())->getTable();
                        $q->select(DB::raw(1))
                            ->from($actionAreaTable)
                            ->whereRaw($actionAreaTable . '.id = action_area_id')
                            ->whereIn($actionAreaTable . '.subject_category_id', $value);
                    })
                        ->orWhereExists(function ($q) use ($value) {
                            $actionAreaTable = (new ActionArea())->getTable();
                            $q->select(DB::raw(1))
                                ->from($actionAreaTable)
                                ->whereRaw($actionAreaTable . '.id = action_area_id')
                                ->whereExists(function ($q) use ($value) {
                                    $closureTable = (new CategoryClosure())->getTable();
                                    $q->select(DB::raw(1))
                                        ->from($closureTable)
                                        ->where($closureTable . '.depth', '!=', 0)
                                        ->whereRaw($closureTable . '.descendant = subject_category_id')
                                        ->whereIn($closureTable . '.ancestor', $value);
                                });
                        });
                });
                break;
            case 'assignee':
                $join->whereRelation('assignee', 'id', (int) $value);
                break;
            case 'priority':
                $join->where('priority', (int) $value);
                break;
            case 'max_impact':
                $join->where('impact', '<=', (int) $value);
                break;
            case 'min_impact':
                $join->where('impact', '>=', (int) $value);
                break;
            case 'start_date':
                try {
                    $date = Carbon::parse($value);
                    $join->where('due_on', '>=', $date);
                    // @codeCoverageIgnoreStart
                } catch (Throwable) {
                }
                // @codeCoverageIgnoreEnd
                break;
            case 'end_date':
                try {
                    $date = Carbon::parse($value);
                    $join->where('due_on', '<=', $date);
                    // @codeCoverageIgnoreStart
                } catch (Throwable) {
                }
                // @codeCoverageIgnoreEnd
                break;
            case 'type':
                if (!$taskable = TaskType::toTaskable($value)) {
                    // @codeCoverageIgnoreStart
                    break;
                }
                // @codeCoverageIgnoreEnd

                $join->where('taskable_type', $taskable);
                break;
                // @codeCoverageIgnoreStart
            default:
                break;
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param Builder              $query
     * @param array<string, mixed> $filtered
     *
     * @return void
     */
    protected function filterStreams(Builder $query, array $filtered = [])
    {
        $availableStreamFilters = [
            'search',
            'streams',
        ];
        $streamFilters = [];

        foreach ($filtered as $key => $filter) {
            if (!in_array($key, $availableStreamFilters)) {
                continue;
            }
            $streamFilters[$key] = $filter;
        }

        if (!empty($streamFilters)) {
            $query->filter($streamFilters);
        }
    }
}
