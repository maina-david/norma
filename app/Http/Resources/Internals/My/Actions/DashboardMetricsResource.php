<?php

namespace App\Http\Resources\Internals\My\Actions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardMetricsResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // @phpstan-ignore-line
            'title' => $this->title, // @phpstan-ignore-line
            'total_tasks' => $this->total_tasks, // @phpstan-ignore-line
            'total_in_progress_tasks' => $this->total_in_progress_tasks, // @phpstan-ignore-line
            'total_not_started_tasks' => $this->total_not_started_tasks, // @phpstan-ignore-line
            'overdue_tasks' => $this->overdue_tasks, // @phpstan-ignore-line
            'completed_total_impact' => $this->completed_total_impact, // @phpstan-ignore-line
            'incomplete_total_impact' => $this->incomplete_total_impact, // @phpstan-ignore-line
        ];
    }
}
