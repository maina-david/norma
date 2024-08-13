<?php

namespace App\Traits;

trait CompilesCSSVariables
{
    /**
     * Get the css variables that are to be applied to the white label
     * e.g. ['primary' => '#f24f3e'].
     *
     * @return array<string, string>
     */
    abstract public function cssVariables(): array;

    /**
     * The default css when nothing is present.
     *
     * @codeCoverageIgnore
     *
     * @return array<string, string>
     */
    private function defaultCssVariables(): array
    {
        return [
            'body-background' => '#F4F7F5',
            'primary' => '#45a8ba',
            'navbar' => '#ffffff',
            'navbar-active' => '#45a8ba',
            'navbar-text' => '#1d1d1d',
        ];
    }

    /**
     * Get the theme css variables to be added.
     *
     * @return string
     */
    public function css(): string
    {
        $css = $this->cssVariables();
        $css = empty($css) ? $this->defaultCssVariables() : $css;

        $css['sidebar-active'] = $css['sidebar-active'] ?? $css['primary'];

        $css = collect($css)
            ->map(fn ($value, $variable) => "--{$variable}:{$value};")
            ->join('');

        return strlen($css) > 0 ? ":root {{$css}}" : '';
    }
}
