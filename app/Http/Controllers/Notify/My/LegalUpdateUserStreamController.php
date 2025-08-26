<?php

namespace App\Http\Controllers\Notify\My;

use App\Http\Controllers\Controller;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class LegalUpdateUserStreamController extends Controller
{
    /**
     * @param LegalUpdate $update
     *
     * @return Response
     */
    public function userStatuses(LegalUpdate $update): Response
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        $baseQuery = $update->users()
            ->inOrganisation($organisation->id);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.notify.legal-update.user-read-statuses',
            'target' => 'legal-update-user-read-statuses-' . $update->id,
            'baseQuery' => $baseQuery,
            'update' => $update,
        ]);

        return turboStreamResponse($view);
    }
}
