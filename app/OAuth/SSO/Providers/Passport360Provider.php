<?php

namespace App\OAuth\SSO\Providers;

/**
 * TODO: Still need to investigate why this is exactly the same as the Regwatch one.
 * Seems we are telling partners that's how the user object should look....
 * (where that was just how Rubicon formats it.) The point of the mapUserToObject is to map
 * whatever format they use to ours...
 */
class Passport360Provider extends RegwatchProvider
{
    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        // @codeCoverageIgnoreStart
        return 'passport360';
        // @codeCoverageIgnoreEnd
    }

    /** {@inheritdoc} */
    protected $scopes = ['openid'];

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
