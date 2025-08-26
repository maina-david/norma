<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;

class ForNormaUserController extends Controller
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
            $baseQuery = User::normaAccess($norma)
                ->inOrganisation($organisation->id);
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? User::normaAccess($norma)
                // @codeCoverageIgnoreStart
                : null;
            // @codeCoverageIgnoreEnd
        }

        /** @var View */
        return view('pages.auth.my.user.settings.for-norma', [
            'baseQuery' => $baseQuery,
            'norma' => $norma,
            'organisation' => $organisation,
        ]);
    }
}
