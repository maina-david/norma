<?php

namespace App\View\Components\Ui\Settings;

use App\Services\Customer\ActiveOrganisationManager;
use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Sidebar extends Component
{
    /** @var array<int, mixed> */
    public array $items = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(protected ActiveOrganisationManager $activeOrgManager)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string|Factory
     */
    public function render()
    {
        $this->items = [
            [
                'icon' => 'browser',
                'label' => __('settings.nav.dashboard'),
                'route' => 'my.settings.dashboard',
            ],
            [
                'icon' => 'map-marker',
                'label' => __('settings.nav.norma_streams'),
                'route' => 'my.settings.normas.index',
            ],
            [
                'icon' => 'users',
                'label' => __('settings.nav.teams'),
                'route' => 'my.settings.teams.index',
            ],
            [
                'icon' => 'user',
                'label' => __('settings.nav.users'),
                'route' => 'my.settings.users.index',
            ],
        ];

        $singleOrg = $this->activeOrgManager->isSingleOrgMode();

        if ($singleOrg || Auth::user()?->can('my.storage.file.global.manage')) {
            $this->items[] = [
                'icon' => 'hdd',
                'label' => __('settings.nav.drives'),
                'route' => 'my.settings.drives.setup',
            ];
        }

        if (!$singleOrg && userCanManageAllOrgs()) {
            $this->items[] = [
                'icon' => 'books',
                'label' => __('settings.nav.libraries'),
                'route' => 'my.settings.libraries.index',
            ];
        }

        if (!$singleOrg && userCanManageAllOrgs()) {
            $this->items[] = [
                'icon' => 'square-check',
                'label' => __('settings.nav.compilation'),
                'route' => 'my.settings.compilation.manual-compilation',
            ];
        }

        if (!$singleOrg && userCanManageAllOrgs()) {
            $this->items[] = [
                'icon' => 'sitemap',
                'label' => __('settings.nav.organisations'),
                'route' => 'my.settings.organisations.index',
            ];
        }

        if (!$singleOrg && auth()->user()?->can('my.notify.legal-update.viewAny')) {
            $this->items[] = [
                'icon' => 'bell',
                'label' => __('settings.nav.legal_updates'),
                'route' => 'my.settings.legal-updates.index',
            ];
        }

        if ($singleOrg && $this->activeOrgManager->getActive()?->hasSSOModule()) {
            $this->items[] = [
                'icon' => 'right-to-bracket',
                'label' => __('settings.nav.sso'),
                'route' => 'my.settings.sso.edit',
            ];
        }

        return view('components.ui.settings.sidebar');
    }
}
