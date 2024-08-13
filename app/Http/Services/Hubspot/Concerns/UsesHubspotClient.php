<?php

namespace App\Http\Services\Hubspot\Concerns;

use HubSpot\Discovery\Discovery;

trait UsesHubspotClient
{
    /**
     * Get the hubspot client.
     *
     * @return Discovery
     */
    protected static function client(): Discovery
    {
        return app(Discovery::class);
    }
}
