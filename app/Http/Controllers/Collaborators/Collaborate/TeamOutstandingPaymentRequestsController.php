<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\Collaborators\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TeamOutstandingPaymentRequestsController extends CollaborateController
{
    protected bool $withCreate = false;
    protected bool $withShow = false;
    protected bool $withUpdate = false;
    protected bool $withDelete = false;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Team::class;
    }

    /**
     * G{@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborators.teams.payment-requests.outstanding';
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        // @phpstan-ignore-next-line
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'view_outstanding' => fn ($row) => static::renderPartial($row, 'view-oustanding-payments-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'title' => __('payments.payment_request.teams_with_outstanding'),
            'headings' => false,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->has('outstandingPaymentRequests');
    }
}
