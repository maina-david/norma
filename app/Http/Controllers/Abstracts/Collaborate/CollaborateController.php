<?php

namespace App\Http\Controllers\Abstracts\Collaborate;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\CrudController;

abstract class CollaborateController extends CrudController
{
    protected bool $sortable = false;

    /**
     * Get the application to be used when authorising the requests.
     *
     * @return ApplicationType
     */
    public static function application(): ApplicationType
    {
        return ApplicationType::collaborate();
    }
}
