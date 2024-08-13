<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForUserLibryoController extends Controller
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
     * @param User $user
     *
     * @return Response
     */
    public function index(User $user): Response
    {
        if ($organisation = $this->activeOrganisationManager->getActive()) {
            $baseQuery = Libryo::userHasAccess($user)
                ->forOrganisation($organisation->id);
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? Libryo::with(['organisation', 'organisation.partner'])->userHasAccess($user)
                // @codeCoverageIgnoreStart
                : Libryo::whereKey(0); // return empty result
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.libryo.for-user',
            'target' => 'settings-libryos-for-user-' . $user->id,
            'baseQuery' => $baseQuery,
            'user' => $user,
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }
}
