<?php

namespace Tests\Feature\Hubspot;

use App\Http\Services\Hubspot\Concerns\AsHubspotContact;
use App\Http\Services\Hubspot\Concerns\HubspotContact;
use App\Http\Services\Hubspot\Contacts;
use App\Http\Services\Hubspot\Exceptions\MissingRequiredProperty;
use App\Models\Auth\User;
use Config;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use HubSpot\Discovery\Discovery;
use HubSpot\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use WithFaker;

    /**
     * Create a generic contact.
     *
     * @param string|null $safeEmail
     *
     * @return HubspotContact
     */
    protected function createGenericSyncContact(?string $safeEmail = null, bool $sync = true): HubspotContact
    {
        return new class($safeEmail, $sync) implements HubspotContact {
            use AsHubspotContact;

            protected static $saved = [];

            protected static $deleted = [];

            public function __construct(protected ?string $email, protected bool $sync = true)
            {
                static::bootAsHubspotContact();
            }

            protected static function saved(callable $callable)
            {
                static::$saved[] = $callable;
            }

            protected static function deleted(callable $callable)
            {
                static::$deleted[] = $callable;
            }

            public function triggerSaved(): void
            {
                collect(static::$saved)->each(fn ($item) => call_user_func($item, $this));
            }

            public function triggerDeleted(): void
            {
                collect(static::$deleted)->each(fn ($item) => call_user_func($item, $this));
            }

            /**
             * Validate if the current contact should sync to Hubspot.
             *
             * @return bool
             */
            public function shouldSyncToHubspot(): bool
            {
                return $this->sync;
            }

            /**
             * Convert the object to a collection of hubspot contact properties.
             *
             * @return SimplePublicObjectInput
             */
            public function toHubspotContact(): SimplePublicObjectInput
            {
                $props = [
                    'firstname' => 'Zebra',
                    'lastname' => 'Corn',
                ];

                if ($this->email) {
                    $props['email'] = $this->email;
                    $props['original_email'] = $this->email;
                }

                return (new SimplePublicObjectInput())->setProperties($props);
            }
        };
    }

    protected function createGenericContact(?string $safeEmail = null, ?int $id = null): HubspotContact
    {
        return new class($safeEmail, $id) implements HubspotContact {
            use AsHubspotContact;

            protected static $saved = [];

            protected static $deleted = [];

            protected $user = null;

            protected $hubspot = null;

            protected $id = null;

            public function __construct(protected ?string $email, ?int $hubspotId = null)
            {
                $this->user = User::factory()->create();
                $this->id = $this->user->id;
                $this->hubspot = $hubspotId
                    ? new class($hubspotId) {
                        public function __construct(public $hubspot_id)
                        {
                        }

                        public function fill($attributes): self
                        {
                            foreach ($attributes as $key => $value) {
                                $this->{$key} = $value;
                            }

                            return $this;
                        }

                        public function save(): void
                        {
                        }
                    }
                : null;
            }

            /**
             * Convert the object to a collection of hubspot contact properties.
             *
             * @return SimplePublicObjectInput
             */
            public function toHubspotContact(): SimplePublicObjectInput
            {
                $props = [
                    'firstname' => 'Zebra',
                    'lastname' => 'Corn',
                ];

                if ($this->email) {
                    $props['email'] = $this->email;
                    $props['original_email'] = $this->email;
                }

                return (new SimplePublicObjectInput())->setProperties($props);
            }

            public function load($param): self
            {
                return $this;
            }

            public function updateQuietly($param): self
            {
                return $this;
            }
        };
    }

    /**
     * Mock the responses.
     *
     * @param array $responses
     *
     * @return void
     */
    protected function createMockedFactory(array $responses): void
    {
        $mock = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);

        $instance = Factory::createWithAccessToken('demo', $client);

        $this->instance(Discovery::class, $instance);
    }

    /**
     * @return void
     */
    public function testItRendersCorrectProperties(): void
    {
        $email = $this->faker->safeEmail();

        $customer = $this->createGenericContact($email);

        $props = collect($customer->toHubspotContact()->getProperties());

        $this->assertEquals($email, $props->get('email'));

        $this->assertEquals('Zebra', $props->get('firstname'));

        $this->assertEquals('Corn', $props->get('lastname'));
    }

    /**
     * @return void
     */
    public function testItCreatesOrUpdatesContacts(): void
    {
        $email = $this->faker->safeEmail();

        $this->createMockedFactory([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'total' => '0',
                    'results' => [],
                    'paging' => [
                        'next' => [
                            'after' => '',
                            'link' => '',
                        ],
                    ],
                ])
            ),
            new Response(
                201,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'id' => $this->faker->numberBetween(1, 2000),
                    'properties' => [
                        'email' => $email,
                    ],
                    'createdAt' => '2019-10-30T03:30:17.883Z',
                    'updatedAt' => '2019-10-30T03:30:17.883Z',
                    'archived' => false,
                ])
            ),
        ]);

        Config::set('services.hubspot.enabled', true);

        $customer = $this->createGenericContact($email);

        $this->assertTrue($customer->updateHubspot());

        $this->createMockedFactory([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'total' => '0',
                    'results' => [],
                    'paging' => [
                        'next' => [
                            'after' => '',
                            'link' => '',
                        ],
                    ],
                ])
            ),
            new Response(
                422,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $this->assertFalse($customer->updateHubspot());

        Config::set('services.hubspot.enabled', false);
    }

    /**
     * @return void
     */
    public function testItCreatesOrUpdatesBulkContacts(): void
    {
        $customers = collect(range(1, 5))->map(fn ($index) => $this->createGenericContact($this->faker->safeEmail()));

        $search = $customers
            ->map(fn () => new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'total' => '0',
                    'results' => [],
                    'paging' => [
                        'next' => [
                            'after' => '',
                            'link' => '',
                        ],
                    ],
                ])
            ))
            ->toArray();

        $responses = $customers->map(fn ($cust) => [
            'id' => $this->faker->numberBetween(1, 2000),
            'properties' => [
                'email' => $cust->toHubspotContact()->getProperties()['email'],
            ],
            'createdAt' => '2019-10-30T03:30:17.883Z',
            'updatedAt' => '2019-10-30T03:30:17.883Z',
            'archived' => false,
        ])->toArray();

        $this->createMockedFactory([
            ...$search,
            new Response(
                201,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'status' => 'COMPLETE',
                    'results' => $responses,
                    'requestedAt' => '2022-11-21T05:00:32.775Z',
                    'startedAt' => '2022-11-21T05:00:32.775Z',
                    'completedAt' => '2022-11-21T05:00:32.775Z',
                    'links' => [],
                ])
            ),
        ]);

        Config::set('services.hubspot.enabled', true);

        $this->assertTrue(Contacts::bulkCreateOrUpdate($customers));

        Config::set('services.hubspot.enabled', false);
    }

    /**
     * @return void
     */
    public function testItDeletesContacts(): void
    {
        $this->createMockedFactory([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['vid' => $this->faker->numberBetween(1, 2000)])
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['vid' => $this->faker->numberBetween(1, 2000), 'deleted' => true])
            ),
        ]);

        $email = $this->faker->safeEmail();

        $customer = $this->createGenericContact($email, 10);

        Config::set('services.hubspot.enabled', true);

        $this->assertTrue($customer->deleteFromHubspot());

        $this->createMockedFactory([
            new Response(
                422,
                ['Content-Type' => 'application/json'],
            ),
            new Response(
                422,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $this->assertFalse(Contacts::delete($customer));

        Config::set('services.hubspot.enabled', false);
    }

    /**
     * @return void
     */
    public function testItThrowsAnExceptionOnMissingEmailOnUpdate(): void
    {
        $customer = $this->createGenericContact();

        $this->expectException(MissingRequiredProperty::class);

        Config::set('services.hubspot.enabled', true);

        $customer->updateHubspot();

        Config::set('services.hubspot.enabled', false);
    }

    /**
     * @return void
     */
    public function testItThrowsAnExceptionOnMissingEmailonDelete(): void
    {
        $customer = $this->createGenericContact();

        $this->expectException(MissingRequiredProperty::class);

        Config::set('services.hubspot.enabled', true);

        $customer->deleteFromHubspot();

        Config::set('services.hubspot.enabled', false);
    }

    /**
     * @return void
     */
    public function testItPreventsSyncing(): void
    {
        $email = $this->faker->safeEmail();

        $customer = $this->createGenericSyncContact($email, false);

        Config::set('services.hubspot.enabled', true);

        $customer->triggerSaved();

        $customer->triggerDeleted();

        $this->assertFalse($customer->updateHubspot());

        $this->assertFalse($customer->deleteFromHubspot());

        Config::set('services.hubspot.enabled', false);
    }

    /**
     * @return void
     */
    public function testItDoesntSyncWhenDisabled(): void
    {
        $email = $this->faker->safeEmail();

        $customer = $this->createGenericSyncContact($email, false);

        Config::set('services.hubspot.enabled', false);

        $this->assertFalse($customer->updateHubspot());
        $this->assertFalse($customer->deleteFromHubspot());
        $this->assertFalse(Contacts::bulkCreateOrUpdate(collect([$customer])));
        $this->assertFalse(Contacts::createOrUpdate($customer));
        $this->assertFalse(Contacts::delete($customer));
        $this->assertFalse(Contacts::bulkCreateOrUpdate(collect([$customer])));
    }
}
