<?php

namespace App\Http\Services\Hubspot\Concerns;

use App\Http\Services\Hubspot\Exceptions\MissingRequiredProperty;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;

interface HubspotContact
{
    /**
     * Convert the object to a collection of hubspot contact properties.
     *
     * @return SimplePublicObjectInput
     */
    public function toHubspotContact(): SimplePublicObjectInput;

    /**
     * Validate if the current contact should sync to Hubspot.
     *
     * @return bool
     */
    public function shouldSyncToHubspot(): bool;

    /**
     * Sync to hubspot if allowed.
     *
     * @throws MissingRequiredProperty
     *
     * @return bool
     */
    public function updateHubspot(): bool;

    /**
     * Remove from hubspot if allowed.
     *
     * @throws MissingRequiredProperty
     *
     * @return bool
     */
    public function deleteFromHubspot(): bool;

    /**
     * Get the email used by the contact.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Get the Hubspot ID on the given contact.
     *
     * @return string|int|null
     */
    public function getHubspotID(): string|int|null;

    /**
     * Set the Hubspot ID on the given contact.
     *
     * @param string|int $hubspotId
     *
     * @return $this
     */
    public function setHubspotID(string|int $hubspotId): self;
}
