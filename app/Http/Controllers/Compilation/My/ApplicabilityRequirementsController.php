<?php

namespace App\Http\Controllers\Compilation\My;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Http\Controllers\Controller;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicabilityRequirementsController extends Controller
{
    /**
     * Load the listing containing works.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $organisation = app(ActiveNormasManager::class)->getActiveOrganisation();
        $this->authorize('access.org.settings', $organisation);

        $this->logActivity($request);

        /** @var View */
        return view('pages.compilation.my.context-question.requirements-setup');
    }

    protected function logActivity(Request $request): void
    {
        if (empty($request->query())) {
            event(new GenericActivityUsingAuth(UserActivityType::viewedApplicabilityRequirements()));
        }
    }
}
