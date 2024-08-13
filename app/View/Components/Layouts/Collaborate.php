<?php

namespace App\View\Components\Layouts;

use App\Models\Auth\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class Collaborate extends LayoutComponent
{
    /** @var User|null */
    public ?User $user;

    /**
     * Create a new component instance.
     *
     * @param bool $fluid
     * @param bool $noPadding
     */
    public function __construct(public bool $fluid = false, public bool $noPadding = false)
    {
        parent::__construct();
        /** @var User $user */
        $user = Auth::user();
        $this->user = $user;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.layouts.collaborate');
    }
}
