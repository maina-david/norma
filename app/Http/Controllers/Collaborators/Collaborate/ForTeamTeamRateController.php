<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Collaborators\TeamRateRequest;
use App\Models\Collaborators\Team;
use App\Models\Collaborators\TeamRate;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ForTeamTeamRateController extends CollaborateController
{
    protected bool $searchable = false;
    protected bool $withShow = false;

    protected TeamRate $teamRate;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return TeamRate::class;
    }

    protected static function forResource(): ?string
    {
        return Team::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborators.teams.team-rates';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return TeamRateRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table. Use the dot notation for relations.
     * e.g. ['profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'task_type' => fn ($row) => $row->taskType->name,
            'rate_per_unit',
            'unit_type_singular',
            'unit_type_plural',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'team' => $this->getForResource(),
            'view' => 'pages.collaborators.collaborate.team.for-team-index',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'team' => $request->route('team'),
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Team */
        $team = $this->getForResource();

        return $team->rates()->getQuery()->with(['taskType']);
    }

    /**
     * @param Team          $team
     * @param TeamRate|null $teamRate
     *
     * @return array<string, int>
     */
    private function getTaskTypes(Team $team, ?TeamRate $teamRate = null): array
    {
        $team->load(['rates.taskType']);
        $taskTypes = $team->rates->map(function ($rate) {
            /** @var TeamRate $rate */
            return $rate->taskType;
        });

        return TaskType::whereNotIn('id', $taskTypes->modelKeys())
            ->when($teamRate, fn ($q) => $q->orWhere('id', $teamRate?->taskType?->id))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        /** @var Team */
        $team = $this->getForResource();
        $teamRateId = $request->route('team_rate');
        /** @var TeamRate */
        $teamRate = TeamRate::findOrFail($teamRateId);

        return [
            'taskTypes' => $this->getTaskTypes($team, $teamRate),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        /** @var Team */
        $team = $this->getForResource();

        return [
            'taskTypes' => $this->getTaskTypes($team),
        ];
    }
}
