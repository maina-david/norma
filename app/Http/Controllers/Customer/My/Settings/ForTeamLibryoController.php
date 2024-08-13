<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Actions\Customer\Team\AddLibryos;
use App\Actions\Customer\Team\RemoveLibryos;
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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ForTeamLibryoController extends Controller
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
            $baseQuery = Libryo::forTeam($team->id)
                ->forOrganisation($organisation->id);
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? Libryo::with(['organisation', 'organisation.partner'])->forTeam($team->id)
                // @codeCoverageIgnoreStart
                : Libryo::whereKey(0); // return empty result
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.libryo.for-team',
            'target' => 'settings-libryos-for-team-' . $team->id,
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
        /** @var \Illuminate\Database\Eloquent\Collection<Libryo> */
        $libryos = $this->filterActionInputForOrg($request, Libryo::class, $organisation);

        $flashMessage = $this->performAction($actionName, $team, $libryos);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.libryos.for.team.index', ['team' => $team->id]));

        return $response;
    }

    /**
     * @param string             $action
     * @param Team               $team
     * @param Collection<Libryo> $libryos
     *
     * @return string
     */
    private function performAction(string $action, Team $team, Collection $libryos): string
    {
        switch ($action) {
            case 'remove_from_team':
                RemoveLibryos::run($team, $libryos);
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
    public function addLibryos(Request $request, Team $team): RedirectResponse
    {
        $libryoIds = $request->input('libryos', []);

        $organisation = $this->activeOrganisationManager->getActive();

        AddLibryos::run($team, $libryoIds, $organisation);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.libryos.for.team.index', ['team' => $team->id]));

        return $response;
    }
}
