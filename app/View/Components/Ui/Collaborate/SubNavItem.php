<?php

namespace App\View\Components\Ui\Collaborate;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class SubNavItem extends Component
{
    /** @var string */
    public ?string $route;

    /** @var string */
    public ?string $icon;

    /** @var array<mixed> */
    public array $routeParams = [];

    /** @var string */
    public string $classes;

    /**
     * @param Request             $request
     * @param array<string,mixed> $item
     */
    public function __construct(Request $request, array $item = [])
    {
        $this->route = $item['route'] ?? null;

        $this->icon = $item['icon'] ?? null;

        $this->routeParams = $item['routeParams'] ?? [];

        $this->classes = 'cursor-pointer group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:text-primary';

        $this->classes .= $this->route && $request->routeIs($this->route)
            ? ' bg-libryo-gray-100 text-primary '
            : ' text-libryo-gray-900';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.ui.collaborate.sub-nav-item');
    }
}
