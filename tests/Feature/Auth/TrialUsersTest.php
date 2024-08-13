<?php

namespace Tests\Feature\Auth;

use App\Jobs\Auth\RemoveTrialUsers;
use App\Models\Auth\User;
use Tests\TestCase;

class TrialUsersTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRemovesTrialUsers(): void
    {
        $user = User::factory()->create();
        $user->trial()->create(['expires_at' => now()->addMinute()]);

        RemoveTrialUsers::dispatchSync();

        $this->assertModelExists($user);

        $this->travel(5)->minutes();

        RemoveTrialUsers::dispatchSync();

        $this->assertModelMissing($user);
    }

    //    /**
    //     * TODO: uncomment
    //     * @return void
    //     */
    //    public function testItRunsOnASchedule(): void
    //    {
    //        $this->travelTo(now()->setTime(18, 00));
    //
    //        $user = User::factory()->create();
    //
    //        $user->trial()->create(['expires_at' => now()]);
    //
    //        $this->travelTo(now()->setTime(19, 00));
    //
    //        $this->assertModelExists($user);
    //
    //        Artisan::call('schedule:run');
    //
    //        $this->assertModelMissing($user);
    //    }
}
