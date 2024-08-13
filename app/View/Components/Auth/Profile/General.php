<?php

namespace App\View\Components\Auth\Profile;

use Closure;
use Illuminate\View\Component;

/**
 * @codeCoverageIgnore
 */
class General extends Component
{
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
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.auth.profile.general');
    }
}
