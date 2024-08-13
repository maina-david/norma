<?php

namespace Tests\Unit\Listeners\Auth;

use App\Events\Auth\UserActivity\ActivatedLibryo;
use App\Events\Auth\UserActivity\UserAccessedPlatform;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Tests\TestCase;

class UserActivitySubscriberTest extends TestCase
{
    /**
     * @return void
     */
    public function testActivateLibryoEvent(): void
    {
        $user = User::factory()->create();
        $libryo = Libryo::factory()->create();

        $this->assertTrue($user->activities()->count() === 0);

        ActivatedLibryo::dispatch($user, $libryo);

        $this->assertTrue($user->activities()->count() > 0);
    }

    /**
     * @return void
     */
    public function testUserActivity(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->activities()->count() === 0);

        event(new UserAccessedPlatform($user));

        $this->assertTrue($user->activities()->count() > 0);
    }
}
