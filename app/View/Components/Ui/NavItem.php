<?php

namespace App\View\Components\Ui;

use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class NavItem extends Component
{
    /** @var string|null */
    public ?string $route;

    /** @var array<string, mixed> */
    public array $routeParams;

    /** @var string */
    public string $classes;

    /**
     * @param Request              $request
     * @param string|null          $route
     * @param array<string, mixed> $routeParams
     * @param bool                 $block
     */
    public function __construct(Request $request, ?string $route = null, array $routeParams = [], bool $block = false)
    {
        $this->route = $route;
        $this->routeParams = $routeParams;

        $this->classes = 'cursor-pointer items-center pt-1 text-sm font-medium pb-2 mb-2 sm:mb-0 ';

        $this->classes .= $block
            ? 'block px-4 sm:py-2 border-none '
            : 'inline-flex text-navbar-text sm:pb-0 px-2 border-b-2 ';

        $this->classes .= $route && $request->routeIs($route)
            ? 'border-navbar-active text-navbar-active font-bold'
            : 'border-libryo-gray-200 sm:border-transparent hover:text-navbar-active hover:border-navbar-active';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Factory|Closure|string
     */
    public function render()
    {
        return view('components.ui.nav-item');
    }
}
