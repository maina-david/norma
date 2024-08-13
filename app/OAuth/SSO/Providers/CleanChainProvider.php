<?php

namespace App\OAuth\SSO\Providers;

/**
 * TODO: Still need to investigate why this is exactly the same as the Regwatch one.
 * Seems we are telling partners that's how the user object should look....
 * (where that was just how Rubicon formats it.) The point of the mapUserToObject is to map
 * whatever format they use to ours...
 * Should not really extend Regwatch... but for now don't want to break integrations. Then should
 * be able to remove getAuthUrl as that's just the same as the Abstract class - had to overrid Regwatch one here.
 */
class CleanChainProvider extends RegwatchProvider
{
    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        // @codeCoverageIgnoreStart
        return 'cleanchain';
        // @codeCoverageIgnoreEnd
    }

    /** {@inheritdoc} */
    protected $scopes = [''];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        // @codeCoverageIgnoreStart
        return $this->buildAuthUrlFromBase(config('services.sso.' . $this->getIdentifier() . '.authorize_url'), $state);
        // @codeCoverageIgnoreEnd
    }
}
