<?php

namespace App\View\Components\Ui;

use App\Managers\ThemeManager;
use Closure;
use Illuminate\View\Component;

class LibryoLogo extends Component
{
    /** @var string */
    public string $height;

    /** @var string */
    public string $source;

    /**
     * Create a new component instance.
     *
     * @param \App\Managers\ThemeManager $themeManager
     * @param bool                       $login
     * @param string                     $height
     */
    public function __construct(protected ThemeManager $themeManager, bool $login = false, string $height = 'h-12')
    {
        $theme = $themeManager->current();
        $this->height = $height;
        $this->source = $login ? $theme->loginLogo() : $theme->appLogo();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.ui.libryo-logo');
    }
}
