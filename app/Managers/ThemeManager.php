<?php

namespace App\Managers;

use App\Contracts\Themes\Theme as ThemeContract;
use App\Themes\Theme;
use Illuminate\Support\Facades\Session;

class ThemeManager
{
    /**
     * Cache of the enabled themes.
     *
     * @var ThemeContract[]
     */
    protected static array $cache = [];

    /**
     * Register the provided themes for use in the app. Each theme should
     * extend this class.
     *
     * @param ThemeContract[] $themes
     *
     * @return void
     */
    public function register(array $themes): void
    {
        collect($themes)->each(function (ThemeContract $theme) {
            self::$cache[$theme->shortname()] = $theme;
        });
    }

    /**
     * Get the current theme.
     *
     * @return ThemeContract
     */
    public function current(): ThemeContract
    {
        return Session::get('app.theme', new Theme());
    }

    /**
     * Activate the theme if present.
     *
     * @param string $theme
     *
     * @return void
     */
    public function activate(string $theme): void
    {
        $theme = self::$cache[$theme] ?? new Theme();

        Session::put('app.theme', $theme);
    }
}
