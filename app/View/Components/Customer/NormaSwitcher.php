<?php

namespace App\View\Components\Customer;

use App\Enums\Customer\NormaSwitcherMode;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Services\Customer\ActiveNormasManager;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class NormaSwitcher extends Component
{
    /** @var NormaSwitcherMode */
    public NormaSwitcherMode $mode;

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $this->mode = $manager->getMode();

        $norma = $manager->getActive($user);

        // default is single mode, but if the user doesn't have a norma, switch to all mode
        if (NormaSwitcherMode::single()->is($this->mode) && is_null($norma)) {
            // if no active norma in session, get last used one
            /** @var Norma|null */
            $norma = $manager->get($user, 1)->first();

            // if still null, then switch to all mode
            if (is_null($norma)) {
                $manager->activateAll($user);
            }
        }

        $organisation = $manager->getActiveOrganisation();

        $viewData = [
            'active' => [
                'id' => $norma?->id,
                'title' => $norma?->title,
            ],
            'organisation' => $organisation,
            'isAllMode' => NormaSwitcherMode::all()->is($this->mode),
            'lastActive' => $manager->get($user, 1)->first(),
        ];

        return view('components.customer.norma-switcher', $viewData);
    }
}
