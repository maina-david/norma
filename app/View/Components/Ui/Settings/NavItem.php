<?php

namespace App\View\Components\Ui\Settings;

use Closure;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class NavItem extends Component
{
    /** @var string */
    public ?string $route;

    /** @var string */
    public ?string $icon;

    /** @var string */
    public string $classes;

    /**
     * @param Request          $request
     * @param string|null|null $route
     * @param string|null|null $icon
     */
    public function __construct(Request $request, ?string $route = null, ?string $icon = null)
    {
        $this->route = $route;

        $this->icon = $icon;

        $this->classes = 'cursor-pointer text-libryo-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:text-primary';

        $this->classes .= $route && $request->routeIs($route)
            ? ' bg-libryo-gray-100'
            : '';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.ui.settings.nav-item');
    }
}
