<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Collaborators\TeamBankDetailsRequest;
use App\Http\Requests\Collaborators\TeamRequest;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TeamController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'title';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
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
        return 'collaborate.teams';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return TeamRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table. Use the dot notation for relations.
     * e.g. ['profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return ['title', 'created_at'];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'team' => Team::find($request->route('team')),
            'formWidth' => 'w-full',
            'noCard' => true,
        ];
    }

    public function editMyBilling(Request $request): View
    {
        /** @var Collaborator */
        $user = Collaborator::with(['profile.team'])->find($request->user()?->id);
        $this->authorize('manage', $user->profile->team);

        /** @var View */
        return view('pages.collaborators.collaborate.team.edit-billing', [
            'resource' => $user->profile->team,
        ]);
    }

    public function myPayments(Request $request): View
    {
        /** @var Collaborator */
        $user = Collaborator::with(['profile.team'])->find($request->user()?->id);
        $this->authorize('manage', $user->profile->team);

        /** @var View */
        return view('pages.collaborators.collaborate.team.my-payments-index', [
            'team' => $user->profile->team,
        ]);
    }

    public function updateMyBilling(TeamBankDetailsRequest $request): RedirectResponse
    {
        /** @var Collaborator */
        $user = Collaborator::with(['profile.team'])->find($request->user()?->id);
        $team = $user->profile->team;

        $team->fill($request->validated())
            ->save();

        Session::flash('flash.message', __('actions.success'));

        return redirect()->route('collaborate.collaborators.teams.billing.edit');
    }
}
