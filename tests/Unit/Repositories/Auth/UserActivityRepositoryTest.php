<?php

namespace Tests\Unit\Repositories\Auth;

use App\Events\Auth\UserActivity\UserAccessedPlatform;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Repositories\Auth\UserActivityRepository;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserActivityRepositoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testAddUserActivityEvent(): void
    {
        $user = User::factory()->create();
        app(UserActivityRepository::class)->addUserActivityEvent(new UserAccessedPlatform($user, ['origin' => 'test']));

        $this->assertTrue($user->activities->isNotEmpty());
    }

    /**
     * @return void
     */
    public function testForgetActivityForUser(): void
    {
        $user = User::factory()->create();

        app(UserActivityRepository::class)->addUserActivityEvent(new UserAccessedPlatform($user, ['origin' => 'test']));
        $activity = UserActivity::factory()->for($user)->create(['details' => '{"user": {"fname": "John Doe"}}']);

        $this->assertStringContainsString('John Doe', $activity->details);
        app(UserActivityRepository::class)->forgetActivityForUser($user);
        $activity = $activity->fresh();
        $this->assertStringNotContainsString('John Doe', $activity->details);
        $this->assertStringContainsString('Forgotten', $activity->details);
    }

    /**
     * @return void
     */
    public function testFirePlatformAccessedEvent(): void
    {
        Event::fake();
        // have to set settings to empty, as the event fake is preventing observer from creating default settings
        $user = User::factory()->create(['settings' => []]);

        app(UserActivityRepository::class)->firePlatformAccessedEvent($user);
        Event::assertDispatched(UserAccessedPlatform::class);
    }
}
