<?php

namespace App\View\Components\Customer;

use App\Services\Customer\ActiveOrganisationManager;
use Closure;
use Illuminate\View\Component;

class OrganisationSwitcher extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(protected ActiveOrganisationManager $activeOrganisationManager)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        $organisation = $this->activeOrganisationManager->getActive();

        $viewData = [
            'active' => [
                'id' => $organisation->id ?? null,
                'title' => $organisation->title ?? __('customer.organisation.all_organisations'),
            ],
        ];

        return view('components.customer.organisation-switcher', $viewData);
    }
}
