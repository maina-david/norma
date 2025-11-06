<?php

namespace App\Services\Modules;

use App\Enums\System\NormaModule;
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
            return NormaModule::dashboard()->value;
        }
        if ($this->request->routeIs('my.assess.*')) {
            return NormaModule::comply()->value;
        }
        if ($this->request->routeIs('my.notify.*')) {
            return NormaModule::updates()->value;
        }
        if ($this->request->routeIs('my.corpus.*')) {
            return NormaModule::corpus()->value;
        }
        if ($this->request->routeIs('my.tasks.*')) {
            return NormaModule::tasks()->value;
        }
        if ($this->request->routeIs('my.drives.*')) {
            return NormaModule::drives()->value;
        }
        if ($this->request->routeIs('my.actions.*')) {
            return NormaModule::actions()->value;
        }

        // @codeCoverageIgnoreStart
        return '';
        // @codeCoverageIgnoreEnd
    }
}
