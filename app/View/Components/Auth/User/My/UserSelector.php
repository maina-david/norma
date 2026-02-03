<?php

namespace App\View\Components\Auth\User\My;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class UserSelector extends Component
{
    /** @var array<int|string,string> */
    public array $users = [];

    /**
     * @param int|string|array<int>|null $value
     * @param string                     $name
     * @param string                     $label
     * @param bool                       $multiple
     * @param bool                       $hashId
     * @param bool                       $organisation
     */
    public function __construct(
        public int|string|array|null $value,
        public string $name = '',
        public string $label = '',
        public bool $multiple = false,
        public bool $hashId = false,
        public bool $organisation = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        if (!$this->organisation && $manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $usersQuery = User::normaAccess($norma)->inOrganisation($organisation->id);
        } else {
            $usersQuery = $organisation->users();
        }

        $usersQuery->orderBy('fname');

        foreach ($usersQuery->active()->get(['id', 'sname', 'fname']) as $user) {
            /** @var User $user */
            $this->users[$this->hashId ? $user->hash_id : $user->id] = $user->full_name;
        }

        /** @var User */
        $user = Auth::user();
        /** @var string */
        $me = __('auth.user.me');

        /** @var View */
        return view('components.auth.user.my.user-selector', [
            'me' => [($this->hashId ? $user->hash_id : $user->id) => $me . ' - ' . $user->full_name],
        ]);
    }
}
