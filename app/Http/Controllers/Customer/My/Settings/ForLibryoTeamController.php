<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Actions\Customer\Libryo\AddTeams;
use App\Actions\Customer\Libryo\RemoveTeams;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ForLibryoTeamController extends Controller
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
     * @param Libryo $libryo
     *
     * @return View
     */
    public function index(Libryo $libryo): View
    {
        if ($organisation = $this->activeOrganisationManager->getActive()) {
            $baseQuery = $libryo->teams()
                ->forOrganisation($organisation->id)
                ->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? $libryo->teams()->getQuery()
                // @codeCoverageIgnoreStart
                : Team::whereKey(0); // forces an empty empty response
            // @codeCoverageIgnoreEnd
        }

        /** @var View */
        return view('pages.customer.my.team.settings.for-libryo', [
            'baseQuery' => $baseQuery,
            'libryo' => $libryo,
            'organisation' => $organisation,
        ]);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request                $request
     * @param Libryo                 $libryo
     * @param Organisation|null|null $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Libryo $libryo, ?Organisation $organisation = null): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var \Illuminate\Database\Eloquent\Collection<Team> */
        $teams = $this->filterActionInputForOrg($request, Team::class, $organisation);

        $flashMessage = $this->performAction($actionName, $libryo, $teams);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.teams.for.libryo.index', ['libryo' => $libryo->id]));

        return $response;
    }

    /**
     * @param string           $action
     * @param Libryo           $libryo
     * @param Collection<Team> $teams
     *
     * @return string
     */
    private function performAction(string $action, Libryo $libryo, Collection $teams): string
    {
        switch ($action) {
            case 'remove_from_libryo':
                RemoveTeams::run($libryo, $teams);
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
     * @param Request $request
     * @param Libryo  $libryo
     *
     * @return RedirectResponse
     */
    public function addTeams(Request $request, Libryo $libryo): RedirectResponse
    {
        $teamIds = $request->input('teams', []);

        $organisation = $this->activeOrganisationManager->getActive();

        AddTeams::run($libryo, $teamIds, $organisation);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.teams.for.libryo.index', ['libryo' => $libryo->id]));

        return $response;
    }
}
