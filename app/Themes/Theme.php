<?php

namespace App\Themes;

use App\Contracts\Themes\Theme as ThemeContract;
use App\Traits\CompilesCSSVariables;

class Theme implements ThemeContract
{
    use CompilesCSSVariables;

    /**
     * Get the short name or alias of the theme.
     *
     * @return string
     */
    public function shortname(): string
    {
        return 'default';
    }

    /**
     * Get the css variables that are to be applied to theme the app.
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return [
            'primary' => '#014E20',
            'primary-lighter' => '#016D2D',
            'primary-darker' => '#013B18',
            'navbar' => '#ffffff',
            'navbar-active' => '#45a8ba',
            'navbar-text' => '#1d1d1d',
            'sidebar-background' => '#0A2B14',
            'sidebar-background-sub' => '#29392d',
            'sidebar-text' => '#D3DFD4',
            'sidebar-active' => '#00FFBC',

            'libryo-gray-50' => '#fafaf9',
            'libryo-gray-100' => '#f5f5f4',
            'libryo-gray-200' => '#e7e5e4',
            'libryo-gray-300' => '#d6d3d1',
            'libryo-gray-400' => '#a8a29e',
            'libryo-gray-500' => '#78716c',
            'libryo-gray-600' => '#57534e',
            'libryo-gray-700' => '#44403c',
            'libryo-gray-800' => '#292524',
            'libryo-gray-900' => '#1c1917',
            'libryo-gray-950' => '#0c0a09',
        ];
    }

    /**
     * Get the login page logo.
     *
     * @return string
     */
    public function loginLogo(): string
    {
        return static::libryoLogo();
    }

    /**
     * Get the main app logo.
     *
     * @return string
     */
    public function appLogo(): string
    {
        return asset('/img/libryo-app.svg');
    }

    /**
     * The location of the libryo logo.
     *
     * @return string
     */
    public static function libryoLogo(): string
    {
        return asset('/img/libryo.png');
    }

    /**
     * Get the main app favicon.
     *
     * @return string
     */
    public function favicon(): string
    {
        return self::libryoFavicon();
    }

    /**
     * Get the main app favicon.
     *
     * @return string
     */
    public static function libryoFavicon(): string
    {
        return asset('/img/favicon.png');
    }

    /**
     * Get the CSS background value for the auth pages.
     *
     * @return string
     */
    public function authBackground(): string
    {
        return '#F4F7F5';
    }
}
