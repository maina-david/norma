<?php

namespace Tests\Unit\Listeners\Auth;

use App\Events\Auth\UserActivity\ActivatedNorma;
use App\Events\Auth\UserActivity\UserAccessedPlatform;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use Tests\TestCase;

class UserActivitySubscriberTest extends TestCase
{
    /**
     * @return void
     */
    public function testActivateNormaEvent(): void
    {
        $user = User::factory()->create();
        $norma = Norma::factory()->create();

        $this->assertTrue($user->activities()->count() === 0);

        ActivatedNorma::dispatch($user, $norma);

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
