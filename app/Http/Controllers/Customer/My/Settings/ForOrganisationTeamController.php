<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForOrganisationTeamController extends Controller
{
    // use PerformsActions;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
    ) {
    }

    /**
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function index(Organisation $organisation): Response
    {
        $baseQuery = $organisation->teams()->getQuery();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.team.for-organisation',
            'target' => 'settings-teams-for-organisation-' . $organisation->id,
            'baseQuery' => $baseQuery,
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }
}
