<?php

namespace App\Contracts\WhiteLabels;

use App\Contracts\Themes\Theme;

interface WhiteLabel extends Theme
{
    /**
     * Get the name of the application. This will be used to set the title bar
     * and also the config('app.name') environment variable.
     *
     * @return string
     */
    public function appName(): string;

    /**
     * Get the subdomain of the application.
     *
     * @return string
     */
    public function shortname(): string;

    /**
     * Get the Auth Provider for this white label.
     *
     * @return string
     */
    public function authProvider(): string;

    /**
     * Get the URLs that are allowed for this white label.
     *
     * @return array<int,string>
     */
    public function urls(): array;
}
