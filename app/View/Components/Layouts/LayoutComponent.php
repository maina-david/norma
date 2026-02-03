<?php

namespace App\View\Components\Layouts;

use App\Managers\AppManager;
use Illuminate\View\Component;

abstract class LayoutComponent extends Component
{
    /** @var string */
    public string $normaApp;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->normaApp = AppManager::getApp();
    }
}
