<?php

namespace App\Livewire\Customer;

use App\Models\Auth\User;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class OrganisationUserSelector extends Component
{
    /**
     * Whether to use the active libryo users instead of organisation.
     *
     * @var bool
     */
    public bool $forLibryo = false;

    /** @var bool */
    public bool $multiple = false;

    /** @var array<int, string> */
    public array $selected = [];

    /**
     * The loading placeholder.
     *
     * @return \Illuminate\View\View
     */
    public function placeholder(): View
    {
        /** @var View */
        return view('partials.ui.render-skeleton', [
            'rows' => 3,
            'noCircle' => true,
            'flat' => true,
        ]);
    }

    /**
     * Get the current users for the organisation or libryo.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUsers(): Collection
    {
        /** @var ActiveLibryosManager $manager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        $query = $organisation->users();

        if ($this->forLibryo && $manager->isSingleMode() && $libryo = $manager->getActive()) {
            $query = User::libryoAccess($libryo)->inOrganisation($organisation->id);
        }

        return $query->orderBy('fname')
            ->active()
            ->get(['id', 'sname', 'fname', 'avatar_attachment_id']);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        /** @var View */
        return view('livewire.customer.organisation-user-selector', [
            'users' => $this->getUsers(),
            'me' => Auth::user(),
        ]);
    }
}
