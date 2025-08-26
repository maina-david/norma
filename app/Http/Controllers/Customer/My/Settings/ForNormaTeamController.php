<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Actions\Customer\Norma\AddTeams;
use App\Actions\Customer\Norma\RemoveTeams;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ForNormaTeamController extends Controller
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
     * @param Norma $norma
     *
     * @return View
     */
    public function index(Norma $norma): View
    {
        if ($organisation = $this->activeOrganisationManager->getActive()) {
            $baseQuery = $norma->teams()
                ->forOrganisation($organisation->id)
                ->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? $norma->teams()->getQuery()
                // @codeCoverageIgnoreStart
                : Team::whereKey(0); // forces an empty empty response
            // @codeCoverageIgnoreEnd
        }

        /** @var View */
        return view('pages.customer.my.team.settings.for-norma', [
            'baseQuery' => $baseQuery,
            'norma' => $norma,
            'organisation' => $organisation,
        ]);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request                $request
     * @param Norma                 $norma
     * @param Organisation|null|null $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Norma $norma, ?Organisation $organisation = null): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var \Illuminate\Database\Eloquent\Collection<Team> */
        $teams = $this->filterActionInputForOrg($request, Team::class, $organisation);

        $flashMessage = $this->performAction($actionName, $norma, $teams);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.teams.for.norma.index', ['norma' => $norma->id]));

        return $response;
    }

    /**
     * @param string           $action
     * @param Norma           $norma
     * @param Collection<Team> $teams
     *
     * @return string
     */
    private function performAction(string $action, Norma $norma, Collection $teams): string
    {
        switch ($action) {
            case 'remove_from_norma':
                RemoveTeams::run($norma, $teams);
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
     * @param Norma  $norma
     *
     * @return RedirectResponse
     */
    public function addTeams(Request $request, Norma $norma): RedirectResponse
    {
        $teamIds = $request->input('teams', []);

        $organisation = $this->activeOrganisationManager->getActive();

        AddTeams::run($norma, $teamIds, $organisation);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.teams.for.norma.index', ['norma' => $norma->id]));

        return $response;
    }
}
