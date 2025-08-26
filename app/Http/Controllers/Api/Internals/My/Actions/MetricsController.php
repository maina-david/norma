<?php

namespace App\Http\Controllers\Api\Internals\My\Actions;

use App\Enums\Tasks\TaskStatus;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController
{
    /**
     * Get the base query.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     * @param \Illuminate\Http\Request                    $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function baseQuery(ActiveNormasManager $manager, Request $request): Builder
    {
        return Task::filter($request->all())
            ->forNormaOrOrganisation($manager->getActive(), $manager->getActiveOrganisation());
    }

    /**
     * @param \App\Services\Customer\ActiveNormasManager $manager
     * @param \Illuminate\Http\Request                    $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statuses(ActiveNormasManager $manager, Request $request): JsonResponse
    {
        $chartData = [
            TaskStatus::notStarted()->value => 0,
            TaskStatus::inProgress()->value => 0,
            TaskStatus::done()->value => 0,
            TaskStatus::paused()->value => 0,
        ];

        $this->baseQuery($manager, $request)
            ->select('task_status', DB::raw('count(*) as total'))
            ->groupBy('task_status')
            ->orderBy('task_status')
            ->get()
            ->each(function ($item) use (&$chartData) {
                $chartData[$item['task_status']] = $item['total'] ?? 0;
            });

        return response()->json(['data' => $chartData]);
    }

    /**
     * Get the impact metric.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     * @param \Illuminate\Http\Request                    $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function impact(ActiveNormasManager $manager, Request $request): JsonResponse
    {
        $overdueImpact = $this->baseQuery($manager, $request)
            ->overdue()
            ->statusIncomplete()
            ->sum('impact');

        $taskData = $this->baseQuery($manager, $request)
            ->select('task_status', DB::raw('sum(impact) as total_impact'))
            ->groupBy('task_status')
            ->orderBy('task_status')
            ->get()
            ->all();

        $chartData = [
            TaskStatus::notStarted()->value => 0,
            TaskStatus::inProgress()->value => 0,
            TaskStatus::done()->value => 0,
            TaskStatus::paused()->value => 0,
            4 => $overdueImpact,
        ];

        foreach ($taskData as $item) {
            $chartData[$item['task_status']] = $item['total_impact'] ?? 0;
        }

        return response()->json(['data' => $chartData]);
    }

    /**
     * Get the created vs completed tasks count.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     * @param \Illuminate\Http\Request                    $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function creationCompletion(ActiveNormasManager $manager, Request $request): JsonResponse
    {
        $completedData = $this->baseQuery($manager, $request)
            ->where('completed_at', '>=', now()->subMonths(12))
            ->select(DB::raw("DATE_FORMAT(completed_at, '%Y-%m') AS `x`"), DB::raw('COUNT(*) AS `y`'))
            ->groupBy('x')
            ->orderBy('x')
            ->get()
            ->all();

        $monthsCompleted = collect(range(1, 12))
            ->mapWithKeys(function ($i) {
                $date = now()->subMonths(12 - $i)->format('Y-m');

                return [$date => 0];
            })
            ->all();

        foreach ($completedData as $item) {
            if (isset($monthsCompleted[$item['x']])) {
                $monthsCompleted[$item['x']] = $item['y'] ?? 0;
            }
        }

        ksort($monthsCompleted);

        $createdData = $this->baseQuery($manager, $request)
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') AS `x`"), DB::raw('COUNT(*) AS `y`'))
            ->groupBy('x')
            ->orderBy('x')
            ->get()
            ->all();

        $first = $createdData[0]['x'] ?? null;
        $labels = array_keys($monthsCompleted);

        if ($first) {
            foreach ($labels as $index => $month) {
                if ($month === $first) {
                    break;
                }
                unset($monthsCompleted[$month], $completedData[$index]);
            }
        }

        return response()->json([
            'data' => [
                'labels' => array_keys($monthsCompleted),
                'completed' => array_values($monthsCompleted),
                'created' => array_values($createdData),
            ],
        ]);
    }

    /**
     * Get the provided metric.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     * @param \Illuminate\Http\Request                    $request
     * @param string                                      $metric
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleMetric(ActiveNormasManager $manager, Request $request, string $metric): JsonResponse
    {
        $data = match ($metric) {
            'total' => $this->baseQuery($manager, $request)->count(),
            'overdue' => $this->baseQuery($manager, $request)
                ->statusIncomplete()
                ->overdue()
                ->count(),
            'complete-rate' => round($this->baseQuery($manager, $request)
                ->select(DB::raw('(SUM(CASE WHEN `task_status` = 2 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as completion_percentage'))
                ->value('completion_percentage'), 2),
            'incomplete-rate' => round($this->baseQuery($manager, $request)
                ->select(DB::raw('(SUM(CASE WHEN `task_status` = 0 THEN 1 WHEN `task_status` = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as `incomplete_percentage`'))
                ->value('incomplete_percentage'), 2),
            default => abort(404)
        };

        return response()->json(['data' => ['value' => $data]]);
    }
}
