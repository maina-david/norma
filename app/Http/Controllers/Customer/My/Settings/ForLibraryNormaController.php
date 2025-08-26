<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForLibraryNormaController extends Controller
{
    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
    ) {
    }

    /**
     * @param Library $library
     *
     * @return Response
     */
    public function index(Library $library): Response
    {
        if ($organisation = $this->activeOrganisationManager->getActive()) {
            $baseQuery = $library->normas()->forOrganisation($organisation->id)->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? $library->normas()->with(['organisation', 'organisation.partner'])->getQuery()
                // @codeCoverageIgnoreStart
                : Norma::whereKey(0); // return empty result
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.norma.for-library',
            'target' => 'settings-normas-for-library-' . $library->id,
            'baseQuery' => $baseQuery,
            'library' => $library,
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }
}
