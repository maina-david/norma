<?php

namespace App\Services\Modules;

use App\Enums\System\LibryoModule;
use Illuminate\Http\Request;

/**
 * @see Tests\Feature\System\My\SystemNotificationControllerTest for tests that cover this
 */
class CurrentModule
{
    public function __construct(protected Request $request)
    {
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function get(): string
    {
        if ($this->request->routeIs('my.dashboard')) {
            return LibryoModule::dashboard()->value;
        }
        if ($this->request->routeIs('my.assess.*')) {
            return LibryoModule::comply()->value;
        }
        if ($this->request->routeIs('my.notify.*')) {
            return LibryoModule::updates()->value;
        }
        if ($this->request->routeIs('my.corpus.*')) {
            return LibryoModule::corpus()->value;
        }
        if ($this->request->routeIs('my.tasks.*')) {
            return LibryoModule::tasks()->value;
        }
        if ($this->request->routeIs('my.drives.*')) {
            return LibryoModule::drives()->value;
        }
        if ($this->request->routeIs('my.actions.*')) {
            return LibryoModule::actions()->value;
        }

        // @codeCoverageIgnoreStart
        return '';
        // @codeCoverageIgnoreEnd
    }
}
