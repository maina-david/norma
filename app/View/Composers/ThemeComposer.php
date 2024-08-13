<?php

namespace App\View\Composers;

use App\Managers\ThemeManager;
use Illuminate\View\View;

class ThemeComposer
{
    public function __construct(protected ThemeManager $themeManager)
    {
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('appTheme', $this->themeManager->current());
    }
}
