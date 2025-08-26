<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForOrganisationNormaController extends Controller
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
        $baseQuery = $organisation->normas()->getQuery();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.norma.for-organisation',
            'target' => 'settings-normas-for-organisation-' . $organisation->id,
            'baseQuery' => $baseQuery,
            'organisation' => $organisation,
            'tableFields' => $this->activeOrganisationManager->isSingleOrgMode() ? ['title', 'active'] : ['title', 'active', 'created_by'],
        ]);

        return turboStreamResponse($view);
    }
}
