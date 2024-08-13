<?php

namespace App\View\Components\Layouts;

use App\Managers\AppManager;
use Illuminate\View\Component;

abstract class LayoutComponent extends Component
{
    /** @var string */
    public string $libryoApp;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->libryoApp = AppManager::getApp();
    }
}
