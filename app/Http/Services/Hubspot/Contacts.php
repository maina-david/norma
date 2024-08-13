<?php

namespace App\Http\Services\Hubspot;

use App\Http\Services\Hubspot\Concerns\HubspotContact;
use App\Http\Services\Hubspot\Concerns\UsesHubspotClient;
use App\Http\Services\Hubspot\Exceptions\MissingRequiredProperty;
use HubSpot\Client\Crm\Contacts\ApiException;
use HubSpot\Client\Crm\Contacts\Model\BatchInputSimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Contacts\Model\BatchInputSimplePublicObjectInput;
use HubSpot\Client\Crm\Contacts\Model\BatchResponseSimplePublicObject;
use HubSpot\Client\Crm\Contacts\Model\CollectionResponseWithTotalSimplePublicObjectForwardPaging;
use HubSpot\Client\Crm\Contacts\Model\Filter;
use HubSpot\Client\Crm\Contacts\Model\FilterGroup;
use HubSpot\Client\Crm\Contacts\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObject;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use Illuminate\Support\Collection;
use Throwable;

class Contacts
{
    use UsesHubspotClient;

    /**
     * Bulk Create or Update Contacts.
     *
     * @param Collection<HubspotContact> $contacts
     *
     * @return bool
     */
    public static function bulkCreateOrUpdate(Collection $contacts): bool
    {
        if (!config('services.hubspot.enabled')) {
            return false;
        }

        /** @var Collection $mapped */
        $mapped = $contacts->filter(fn ($item) => $item instanceof HubspotContact && $item->shouldSyncToHubspot())
            ->map(function (HubspotContact $item) {
                $properties = $item->toHubspotContact();
                $clean = $properties->getProperties();
                $exists = self::contactExists($item, $clean['original_email']);

                unset($clean['original_email']);

                if ($exists) {
                    return new SimplePublicObjectBatchInput([
                        'id' => $item->getHubspotID(),
                        'properties' => $clean,
                    ]);
                }

                $properties->setProperties($clean);

                return $properties;
            });

        if ($mapped->isEmpty()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        try {
            $results = collect();

            $create = $mapped->filter(fn ($contact) => $contact instanceof SimplePublicObjectInput)->values();
            $update = $mapped->filter(fn ($contact) => $contact instanceof SimplePublicObjectBatchInput)->values();

            if ($create->isNotEmpty()) {
                $item = new BatchInputSimplePublicObjectInput(['inputs' => $create->toArray()]);
                /** @var BatchResponseSimplePublicObject $response */
                $response = self::client()->crm()->contacts()->batchApi()->create($item);
                $results = $results->merge($response->getResults());
            }

            if ($update->isNotEmpty()) {
                $item = new BatchInputSimplePublicObjectBatchInput(['inputs' => $create->toArray()]);
                /** @var BatchResponseSimplePublicObject $response */
                $response = self::client()->crm()->contacts()->batchApi()->update($item);
                $results = $results->merge($response->getResults());
            }

            $results->each(function (SimplePublicObject $item) use ($contacts) {
                /** @var HubspotContact|null $exists */
                $exists = $contacts->where('email', $item->getProperties()['email'])->first();
                $exists?->setHubspotID($item->getId());
            });

            return true;
            // @codeCoverageIgnoreStart
        } catch (ApiException $t) {
            return false;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Create or update an existing contact on hubspot.
     *
     * @param HubspotContact $contact
     *
     * @throws MissingRequiredProperty
     *
     * @return bool
     */
    public static function createOrUpdate(HubspotContact $contact): bool
    {
        if (!config('services.hubspot.enabled')) {
            return false;
        }

        $properties = $contact->toHubspotContact();

        if (!isset($properties->getProperties()['original_email'])) {
            throw new MissingRequiredProperty('original_email');
        }

        try {
            $props = $properties->getProperties();

            $email = $props['original_email'];

            unset($props['original_email']);

            $properties->setProperties($props);

            /** @var SimplePublicObject $response */
            $response = self::contactExists($contact, $email)
                ? self::client()->crm()->contacts()->basicApi()->update((string) $contact->getHubspotID(), $properties)
                : self::client()->crm()->contacts()->basicApi()->create($properties);

            if ($response->getId()) {
                $contact->setHubspotID($response->getId());

                return true;
            }

            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        } catch (Throwable $t) {
            return false;
        }
    }

    /**
     * Check if the contact is new.
     *
     * @param HubspotContact $contact
     * @param string|null    $originalEmail
     *
     * @throws ApiException
     *
     * @return bool
     */
    public static function contactExists(HubspotContact $contact, ?string $originalEmail = null): bool
    {
        if ($contact->getHubspotID()) {
            return true;
        }

        $exists = self::search($originalEmail ?? $contact->getEmail());

        usleep(300000);

        if ($exists) {
            $contact->setHubspotID($exists->getId());

            return true;
        }

        return false;
    }

    /**
     * Delete an existing contact on hubspot.
     *
     * @param HubspotContact $contact
     *
     * @throws MissingRequiredProperty
     *
     * @return bool
     */
    public static function delete(HubspotContact $contact): bool
    {
        if (!config('services.hubspot.enabled')) {
            return false;
        }

        $properties = $contact->toHubspotContact();

        if (!isset($properties->getProperties()['original_email'])) {
            throw new MissingRequiredProperty('original_email');
        }

        try {
            if (!self::contactExists($contact, $properties->getProperties()['original_email'])) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            /** @var string $id */
            $id = $contact->getHubspotID();

            self::client()->crm()->contacts()->basicApi()->archive($id);

            return true;
        } catch (Throwable $t) {
            return false;
        }
    }

    /**
     * Search for the given email.
     *
     * @param string $email
     *
     * @throws ApiException
     *
     * @return SimplePublicObject|null
     */
    public static function search(string $email): ?SimplePublicObject
    {
        $filter = new Filter();
        $filter->setOperator('EQ')->setPropertyName('email')->setValue($email);

        $filterGroup = new FilterGroup();
        $filterGroup->setFilters([$filter]);

        $searchRequest = new PublicObjectSearchRequest();
        $searchRequest->setFilterGroups([$filterGroup]);

        /** @var CollectionResponseWithTotalSimplePublicObjectForwardPaging $contactsPage */
        $contactsPage = self::client()->crm()->contacts()->searchApi()->doSearch($searchRequest);

        $results = $contactsPage->getResults();

        return $results[0] ?? null;
    }
}
