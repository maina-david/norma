<?php

namespace App\Themes;

class Collaborate extends Theme
{
    /**
     * Get the short name or alias of the theme.
     *
     * @return string
     */
    public function shortname(): string
    {
        return 'collaborate';
    }

    /**
     * Get the css variables that are to be applied to theme the app
     * e.g. ['primary' => '#f24f3e'].
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return [
            'primary' => '#64B775',
            'navbar' => '#ffffff',
            'navbar-active' => '#64B775',
            'navbar-text' => '#111827',
        ];
    }

    /**
     * Get the login page logo.
     *
     * @return string
     */
    public function loginLogo(): string
    {
        return $this->appLogo();
    }

    /**
     * Get the main app logo.
     *
     * @return string
     */
    public function appLogo(): string
    {
        return asset('/img/collaborate-logo.svg');
    }

    /**
     * Get the main app favicon.
     *
     * @return string
     */
    public function favicon(): string
    {
        return asset('/img/collaborate-favicon.png');
    }

    /**
     * Get the CSS background value for the auth pages.
     *
     * @return string
     */
    public function authBackground(): string
    {
        return sprintf('url(%s) no-repeat fixed center / cover rgba(0,0,0, 0.2)', asset('/img/collaborate-background.jpg'));
    }
}
