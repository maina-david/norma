<?php

namespace App\Themes;

/**
 * @codeCoverageIgnore
 */
class My extends Theme
{
    /**
     * Get the short name or alias of the theme.
     *
     * @return string
     */
    public function shortname(): string
    {
        return 'my';
    }

    /**
     * Get the css variables that are to be applied to theme the app
     * e.g. ['primary' => '#f24f3e'].
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return [];
    }
}
