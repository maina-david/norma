<?php

namespace App\View\Components\Layouts;

use Closure;

class Guest extends LayoutComponent
{
    /** @var bool */
    public bool $noLogo;

    /**
     * @param bool $noLogo
     */
    public function __construct(bool $noLogo = false)
    {
        parent::__construct();
        $this->noLogo = $noLogo;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.layouts.guest');
    }
}
