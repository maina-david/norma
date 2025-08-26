<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Actions\Customer\Team\AddNormas;
use App\Actions\Customer\Team\RemoveNormas;
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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ForTeamNormaController extends Controller
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
            $baseQuery = Norma::forTeam($team->id)
                ->forOrganisation($organisation->id);
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? Norma::with(['organisation', 'organisation.partner'])->forTeam($team->id)
                // @codeCoverageIgnoreStart
                : Norma::whereKey(0); // return empty result
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.norma.for-team',
            'target' => 'settings-normas-for-team-' . $team->id,
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
        /** @var \Illuminate\Database\Eloquent\Collection<Norma> */
        $normas = $this->filterActionInputForOrg($request, Norma::class, $organisation);

        $flashMessage = $this->performAction($actionName, $team, $normas);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.normas.for.team.index', ['team' => $team->id]));

        return $response;
    }

    /**
     * @param string             $action
     * @param Team               $team
     * @param Collection<Norma> $normas
     *
     * @return string
     */
    private function performAction(string $action, Team $team, Collection $normas): string
    {
        switch ($action) {
            case 'remove_from_team':
                RemoveNormas::run($team, $normas);
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
    public function addNormas(Request $request, Team $team): RedirectResponse
    {
        $normaIds = $request->input('normas', []);

        $organisation = $this->activeOrganisationManager->getActive();

        AddNormas::run($team, $normaIds, $organisation);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.normas.for.team.index', ['team' => $team->id]));

        return $response;
    }
}
