<?php

namespace App\View\Components\Auth\User\My;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingsNav extends Component
{
    /** @var array<mixed> */
    public array $items = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $this->items = [
            [
                'icon' => 'user',
                'label' => __('auth.user.settings.profile'),
                'route' => 'my.user.settings.profile.show',
            ],
            [
                'icon' => 'envelope',
                'label' => __('auth.user.settings.email'),
                'route' => 'my.user.settings.email.show',
            ],
            [
                'icon' => 'lock-alt',
                'label' => __('auth.user.settings.password'),
                'route' => 'my.user.settings.password.show',
            ],
            [
                'icon' => 'bell',
                'label' => __('auth.user.settings.notifications'),
                'route' => 'my.user.settings.notifications.show',
            ],
            [
                'icon' => 'user-shield',
                'label' => __('auth.user.settings.security'),
                'route' => 'my.user.settings.security.show',
            ],
        ];

        /** @var View */
        return view('components.auth.user.my.settings-nav');
    }
}
