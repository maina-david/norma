<?php

namespace App\Http\Services\Hubspot\Concerns;

use App\Http\Services\Hubspot\Contacts;
use App\Http\Services\Hubspot\Exceptions\MissingRequiredProperty;
use App\Models\Auth\HubspotId;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait AsHubspotContact
{
    /**
     * Convert the object to a collection of hubspot contact properties.
     *
     * @return SimplePublicObjectInput
     */
    abstract public function toHubspotContact(): SimplePublicObjectInput;

    /**
     * Validate if the current contact should sync to Hubspot.
     *
     * @return bool
     */
    public function shouldSyncToHubspot(): bool
    {
        return true;
    }

    /**
     * Boot the trait.
     *
     * @throws MissingRequiredProperty
     *
     * @return void
     */
    public static function bootAsHubspotContact(): void
    {
        static::saved(fn (HubspotContact $model) => $model->updateHubspot());

        static::deleted(fn (HubspotContact $model) => $model->deleteFromHubspot());
    }

    /**
     * @return HasOne
     */
    public function hubspot(): HasOne
    {
        return $this->hasOne(HubspotId::class, 'user_id');
    }

    /**
     * Sync to hubspot if allowed.
     *
     * @throws MissingRequiredProperty
     *
     * @return bool
     */
    public function updateHubspot(): bool
    {
        if (config('services.hubspot.enabled') && $this->shouldSyncToHubspot()) {
            return Contacts::createOrUpdate($this);
        }

        return false;
    }

    /**
     * Remove from hubspot if allowed.
     *
     * @throws MissingRequiredProperty
     *
     * @return bool
     */
    public function deleteFromHubspot(): bool
    {
        if (config('services.hubspot.enabled') && $this->shouldSyncToHubspot()) {
            return Contacts::delete($this);
        }

        return false;
    }

    /**
     * Get the email used by the contact.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getEmail(): string
    {
        /** @var string */
        return $this->email;
    }

    /**
     * Get the Hubspot ID on the given contact.
     *
     * @return string|int|null
     */
    public function getHubspotID(): string|int|null
    {
        $this->load(['hubspot']);

        return $this->hubspot?->hubspot_id;
    }

    /**
     * Set the Hubspot ID on the given contact.
     *
     * @param string|int $hubspotId
     *
     * @return self
     */
    public function setHubspotID(string|int $hubspotId): self
    {
        $this->load(['hubspot']);

        $hubspot = $this->hubspot ?? new HubspotId();
        $hubspot->fill(['user_id' => $this->id, 'hubspot_id' => $hubspotId]);
        $hubspot->save();

        return $this;
    }
}
