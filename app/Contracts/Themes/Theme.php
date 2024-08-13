<?php

namespace App\Contracts\Themes;

interface Theme
{
    /**
     * Get the subdomain of the application.
     *
     * @return string
     */
    public function shortname(): string;

    /**
     * Get the css variables that are to be applied to the white label
     * e.g. ['primary' => '#f24f3e'].
     *
     * @return array<string,string>
     */
    public function cssVariables(): array;

    /**
     * Get the login page logo.
     *
     * @return string
     */
    public function loginLogo(): string;

    /**
     * Get the main app logo.
     *
     * @return string
     */
    public function appLogo(): string;

    /**
     * Get the main app favicon.
     *
     * @return string
     */
    public function favicon(): string;

    /**
     * Get the CSS background value for the auth pages.
     *
     * @return string
     */
    public function authBackground(): string;

    /**
     * Get the theme css variables to be added.
     *
     * @return string
     */
    public function css(): string;
}
