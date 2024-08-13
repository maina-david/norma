<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;

class ForLibryoUserController extends Controller
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
            $baseQuery = User::libryoAccess($libryo)
                ->inOrganisation($organisation->id);
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? User::libryoAccess($libryo)
                // @codeCoverageIgnoreStart
                : null;
            // @codeCoverageIgnoreEnd
        }

        /** @var View */
        return view('pages.auth.my.user.settings.for-libryo', [
            'baseQuery' => $baseQuery,
            'libryo' => $libryo,
            'organisation' => $organisation,
        ]);
    }
}
