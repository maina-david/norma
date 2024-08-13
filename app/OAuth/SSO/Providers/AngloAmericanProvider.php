<?php

namespace App\OAuth\SSO\Providers;

/**
 * @codeCoverageIgnore
 */
class AngloAmericanProvider extends AzureProvider
{
    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'angloamerican';
    }
}
