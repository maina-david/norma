<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Customer\TeamUserStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ForUserTeamController extends Controller
{
    use PerformsActions;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     * @param TeamUserStore             $teamUserStore
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
        protected TeamUserStore $teamUserStore,
    ) {
    }

    /**
     * @param User $user
     *
     * @return Response
     */
    public function indexForUser(User $user): Response
    {
        if ($organisation = $this->activeOrganisationManager->getActive()) {
            $baseQuery = $user->teams()
                ->forOrganisation($organisation->id)
                ->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? $user->teams()->getQuery()
                // @codeCoverageIgnoreStart
                : null;
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.team.for-user',
            'target' => 'settings-teams-for-user-' . $user->id,
            'baseQuery' => $baseQuery,
            'user' => $user,
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request           $request
     * @param User              $user
     * @param Organisation|null $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, User $user, ?Organisation $organisation = null): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var \Illuminate\Database\Eloquent\Collection<Team> */
        $teams = $this->filterActionInputForOrg($request, Team::class, $organisation);

        $flashMessage = $this->performAction($actionName, $user, $teams);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.teams.for.user.index', ['user' => $user->id]));

        return $response;
    }

    /**
     * @param string           $action
     * @param User             $user
     * @param Collection<Team> $teams
     *
     * @return string
     */
    private function performAction(string $action, User $user, Collection $teams): string
    {
        /** @var string $flashMessage */
        $flashMessage = '';
        switch ($action) {
            case 'remove':
                $this->teamUserStore->detachTeams($user, $teams);
                /** @var string $flashMessage */
                $flashMessage = __('actions.success');
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        return $flashMessage;
    }

    /**
     * @return RedirectResponse
     */
    public function addTeams(Request $request, User $user): RedirectResponse
    {
        $teamIds = $request->input('teams', []);

        $organisation = $this->activeOrganisationManager->getActive();

        $query = is_null($organisation)
            ? (new Team())->newQuery()
            : Team::where('organisation_id', $organisation->id);

        /** @var Collection<Team> */
        $teams = $query->whereKey($teamIds)
            ->get(['id']);

        $this->teamUserStore->attachTeams($user, $teams);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.teams.for.user.index', ['user' => $user->id]));

        return $response;
    }
}
