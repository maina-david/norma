<?php

namespace App\Http\Controllers\Payments\Collaborate;

use App\Models\Collaborators\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ForTeamPaymentController extends PaymentController
{
    protected bool $searchable = false;
    protected bool $withShow = false;

    /**
     * {@inheritDoc}
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        /** @var Request $request */
        $request = request();
        /** @var Team $team */
        $team = Team::findOrFail($request->route('team'));

        $this->authorize('viewPayments', $team);
    }

    protected static function forResource(): ?string
    {
        return Team::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborators.teams.payments';
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        $arr = parent::indexColumns();
        unset($arr['team']);

        return $arr;
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
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
        $team = $this->getForResource();

        return $team->payments()->getQuery();
    }

    /**
     * @codeCoverageIgnore
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected static function turboTarget(Request $request): ?string
    {
        return 'collaborate.collaborators.teams.payments.index';
    }
}
