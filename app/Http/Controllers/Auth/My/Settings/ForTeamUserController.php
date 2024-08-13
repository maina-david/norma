<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Actions\Customer\Team\AddUsers;
use App\Actions\Customer\Team\RemoveUsers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ForTeamUserController extends Controller
{
    use PerformsActions;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
    ) {
    }

    /**
     * @param Team $team
     *
     * @return Response
     */
    public function index(Team $team): Response
    {
        if ($organisation = $this->activeOrganisationManager->getActive()) {
            $baseQuery = $team->users()
                ->inOrganisation($organisation->id)
                ->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? $team->users()->getQuery()
                // @codeCoverageIgnoreStart
                : User::whereKey(0);
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.auth.my.user.for-team',
            'target' => 'settings-users-for-team-' . $team->id,
            'baseQuery' => $baseQuery,
            'team' => $team,
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request                $request
     * @param Team                   $team
     * @param Organisation|null|null $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Team $team, ?Organisation $organisation = null): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var \Illuminate\Database\Eloquent\Collection<User> */
        $users = $this->filterActionInputForOrg($request, User::class, $organisation);

        $flashMessage = $this->performAction($actionName, $team, $users);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.users.for.team.index', ['team' => $team->id]));

        return $response;
    }

    /**
     * @param string           $action
     * @param Team             $team
     * @param Collection<User> $users
     *
     * @return string
     */
    private function performAction(string $action, Team $team, Collection $users): string
    {
        switch ($action) {
            case 'remove_from_team':
                RemoveUsers::run($team, $users);
                break;

                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }
        /** @var string $flashMessage */
        $flashMessage = __('actions.success');

        return $flashMessage;
    }

    /**
     * @return RedirectResponse
     */
    public function addUsers(Request $request, Team $team): RedirectResponse
    {
        $userIds = $request->input('users', []);

        $organisation = $this->activeOrganisationManager->getActive();

        AddUsers::run($team, $userIds, $organisation);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.users.for.team.index', ['team' => $team->id]));

        return $response;
    }
}
