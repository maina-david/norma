<?php

namespace Tests\Feature\Console\Auth;

use App\Enums\Application\ApplicationType;
use App\Enums\Auth\UserType;
use App\Http\Services\Hubspot\Exceptions\MissingRequiredProperty;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Config;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use HubSpot\Discovery\Discovery;
use HubSpot\Factory;
use Tests\TestCase;

class SyncCRMUsersTest extends TestCase
{
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
     * @throws MissingRequiredProperty
     *
     * @return void
     */
    public function testItSyncsCorrectUsers(): void
    {
        $role = Role::factory()->create(['title' => 'My Users', 'app' => ApplicationType::my()->value]);
        $user = User::factory()->create(['user_type' => UserType::customer()->value]);
        $user->roles()->attach($role->id);

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
                        'email' => $user->email,
                    ],
                    'createdAt' => '2019-10-30T03:30:17.883Z',
                    'updatedAt' => '2019-10-30T03:30:17.883Z',
                    'archived' => false,
                ])
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    json_encode([
                        'status' => 'COMPLETE',
                        'results' => [
                            [
                                'id' => $this->faker->numberBetween(1, 2000),
                                'properties' => [
                                    'email' => $user->email,
                                ],
                                'createdAt' => '2019-10-30T03:30:17.883Z',
                                'updatedAt' => '2019-10-30T03:30:17.883Z',
                                'archived' => false,
                            ],
                        ],
                        'requestedAt' => '2022-11-21T05:00:32.775Z',
                        'startedAt' => '2022-11-21T05:00:32.775Z',
                        'completedAt' => '2022-11-21T05:00:32.775Z',
                        'links' => [],
                    ]),
                ])
            ),
        ]);

        Config::set('services.hubspot.enabled', true);

        $this->assertTrue($user->updateHubspot());

        $this->artisan('norma:sync-crm-users')
            ->assertSuccessful()
            ->expectsOutput('Successfully processed batch of 5.');

        Config::set('services.hubspot.enabled', false);
    }
}
