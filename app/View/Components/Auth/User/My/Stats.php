<?php

namespace App\View\Components\Auth\User\My;

use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Customer\Norma;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Stats extends Component
{
    public int $daysActive = 0;
    public int $updatesCount = 0;
    public int $normaStreamsCount = 0;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public User $user
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $this->daysActive = UserActivity::accessedPlatformThisMonth($this->user)->count();
        $this->updatesCount = $this->user->legalUpdates()->releasedThisMonth()->count();
        $this->normaStreamsCount = Norma::userHasAccess($this->user)
            ->active()
            ->count();

        /** @var View */
        return view('components.auth.user.my.stats');
    }
}
