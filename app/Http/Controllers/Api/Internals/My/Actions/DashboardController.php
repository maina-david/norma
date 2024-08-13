<?php

namespace App\Http\Controllers\Api\Internals\My\Actions;

use App\Http\Controllers\Api\Internals\My\MyApiController;
use App\Http\Resources\Internals\My\Actions\DashboardMetricsResource;
use App\Jobs\Exports\ActionsDashboardStreamDataExportExcel;
use App\Services\Actions\DashboardStreamDataBuilder;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class DashboardController extends MyApiController
{
    /**
     * Get the metrics for the dashboard.
     *
     * @param \App\Services\Customer\ActiveLibryosManager      $manager
     * @param \App\Services\Actions\DashboardStreamDataBuilder $dataBuilder
     * @param \Illuminate\Http\Request                         $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ActiveLibryosManager $manager, DashboardStreamDataBuilder $dataBuilder, Request $request): AnonymousResourceCollection
    {
        $this->parseSort($request);

        /** @var array<string, mixed> $query */
        $query = $request->query();
        $items = $dataBuilder->buildQuery($manager->getActiveOrganisation()->id, [], $query)
            ->reorder($this->sortBy, $this->sortDirection)
            ->paginate(min($request->get('perPage', 50), 50));

        return DashboardMetricsResource::collection($items);
    }

    /**
     * {@inheritDoc}
     */
    protected function generateExcel(Request $request): array
    {
        /** @var array<string, mixed> $filters */
        $filters = $request->query();
        $filename = Str::random(15) . '.xlsx';
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $columns = [
            'title',
            'total_tasks',
            'total_in_progress_tasks',
            'total_not_started_tasks',
            'overdue_tasks',
            'completed_total_impact',
            'incomplete_total_impact',
        ];
        $job = new ActionsDashboardStreamDataExportExcel($filename, $organisation, $columns, $filters);

        dispatch($job);

        return [$job->getJobStatusId(), route('my.downloads.download.actions.dashboard.excel', ['filename' => $filename], false)];
    }
}
