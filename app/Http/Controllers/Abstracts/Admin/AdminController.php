<?php

namespace App\Http\Controllers\Abstracts\Admin;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\CrudController;

abstract class AdminController extends CrudController
{
    /**
     * Get the application to be used when authorising the requests.
     *
     * @return ApplicationType
     */
    protected static function application(): ApplicationType
    {
        return ApplicationType::admin();
    }
}
