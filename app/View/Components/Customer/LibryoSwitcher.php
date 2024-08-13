<?php

namespace App\View\Components\Customer;

use App\Enums\Customer\LibryoSwitcherMode;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveLibryosManager;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class LibryoSwitcher extends Component
{
    /** @var LibryoSwitcherMode */
    public LibryoSwitcherMode $mode;

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $this->mode = $manager->getMode();

        $libryo = $manager->getActive($user);

        // default is single mode, but if the user doesn't have a libryo, switch to all mode
        if (LibryoSwitcherMode::single()->is($this->mode) && is_null($libryo)) {
            // if no active libryo in session, get last used one
            /** @var Libryo|null */
            $libryo = $manager->get($user, 1)->first();

            // if still null, then switch to all mode
            if (is_null($libryo)) {
                $manager->activateAll($user);
            }
        }

        $organisation = $manager->getActiveOrganisation();

        $viewData = [
            'active' => [
                'id' => $libryo?->id,
                'title' => $libryo?->title,
            ],
            'organisation' => $organisation,
            'isAllMode' => LibryoSwitcherMode::all()->is($this->mode),
            'lastActive' => $manager->get($user, 1)->first(),
        ];

        return view('components.customer.libryo-switcher', $viewData);
    }
}
