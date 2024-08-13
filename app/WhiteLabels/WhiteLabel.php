<?php

namespace App\WhiteLabels;

use App\Contracts\WhiteLabels\WhiteLabel as WhiteLabelContract;
use App\Themes\Theme;

class WhiteLabel extends Theme implements WhiteLabelContract
{
    /**
     * Get the name of the application. This will be used to set the title bar
     * and also the config('app.name') environment variable.
     *
     * @return string
     */
    public function appName(): string
    {
        return config('app.name');
    }

    /**
     * Get the Auth Provider for this white label.
     *
     * @return string
     */
    public function authProvider(): string
    {
        return 'libryo';
    }

    /**
     * Get the URLs that are allowed for this white label.
     *
     * @return array<int, string>
     */
    public function urls(): array
    {
        // @codeCoverageIgnoreStart
        return [];
        // @codeCoverageIgnoreEnd
    }
}
