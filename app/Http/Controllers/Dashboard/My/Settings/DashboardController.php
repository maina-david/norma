<?php

namespace App\Http\Controllers\Dashboard\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Models\Notify\LegalUpdate;
use App\Services\Customer\ActiveOrganisationManager;
use App\Services\DateHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * @param DateHelper $dateHelper
     */
    public function __construct(protected DateHelper $dateHelper)
    {
    }

    /**
     * @return View
     */
    public function dashboard(): View
    {
        /** @var User */
        $user = Auth::user();
        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);

        $organisation = null;
        if ($manager->isSingleOrgMode()) {
            /** @var Organisation $organisation */
            $organisation = $manager->getActive();
            $usersQuery = $organisation->users()->active();
            $teamsQuery = $organisation->teams()->getQuery();
            $normasQuery = $organisation->normas()->getQuery()->active();
            $legalUpdatesQuery = LegalUpdate::forOrganisation($organisation);
        } else {
            $usersQuery = User::active()->getQuery();
            $teamsQuery = (new Team())->newQuery();
            $normasQuery = (new Norma())->newQuery()->active();
            $legalUpdatesQuery = (new LegalUpdate())->newQuery();
        }

        $activeUsersCount = $usersQuery->count();
        $teamsCount = $teamsQuery->count();
        $normasCount = $normasQuery->count();
        $updates = [];
        for ($i = 5; $i >= 0; $i--) {
            [$start, $end] = $this->dateHelper->getMonthStartEnd($i);

            $updates[] = [
                'month' => $start->format('F'),
                'count' => $legalUpdatesQuery->clone()
                    ->sentAfter($start)
                    ->sentBefore($end)
                    ->count(),
            ];
        }

        /** @var View */
        return view('pages.dashboard.my.settings.dashboard', [
            'user' => $user,
            'activeUsersCount' => $activeUsersCount,
            'teamsCount' => $teamsCount,
            'normasCount' => $normasCount,
            'updates' => $updates,
        ]);
    }
}
