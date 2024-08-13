<?php

namespace App\Http\Controllers\Payments\Collaborate;

use App\Actions\Payments\CreatePaymentForOutstanding;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\Collaborators\Team;
use App\Models\Payments\PaymentRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ForTeamOutstandingPaymentRequestsController extends CollaborateController
{
    protected bool $searchable = false;

    protected bool $withCreate = false;
    protected bool $withDelete = false;
    protected bool $withUpdate = false;
    protected bool $withShow = false;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return PaymentRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    public static function shouldAuthorise(): bool
    {
        return false;
    }

    protected static function forResource(): ?string
    {
        return Team::class;
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborators.teams.payment-requests.outstanding';
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => $row->id,
            'units' => fn ($row) => $row->units,
            'task' => fn ($row) => view('partials.ui.link', [
                'href' => route('collaborate.tasks.show', ['task' => $row->task->id]),
                'linkText' => $row->task->title,
            ]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'team' => $this->getForResource(),
            'view' => 'pages.collaborators.collaborate.team.outstanding-payments',
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

        return $team->outstandingPaymentRequests()->getQuery()
            ->with(['task']);
    }

    public function payOutstanding(Request $request, CreatePaymentForOutstanding $createPaymentForOutstanding, Team $team): RedirectResponse
    {
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        $createPaymentForOutstanding->handle($team, now(), $user);
        Session::flash('flash.message', __('payments.payment.successfully_made'));

        return redirect()->route('collaborate.collaborators.teams.payments.index', ['team' => $team]);
    }
}
