<?php

namespace Tests\Unit\Services\Customer;

use App\Enums\Customer\LibryoSwitcherMode;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class ActiveLibryosManagerTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet(): void
    {
        $user = User::factory()->create();
        $libryos = Libryo::factory()->count(10)->create();
        $user->libryos()->attach($libryos->modelKeys());

        $activities = UserActivity::factory()
            ->for($user)
            ->state(new Sequence(
                ['place_id' => $libryos[2]->id],
                ['place_id' => $libryos[4]->id],
                ['place_id' => $libryos[3]->id],
                ['place_id' => $libryos[1]->id],
                ['place_id' => $libryos[3]->id],
                ['place_id' => $libryos[0]->id],
                ['place_id' => $libryos[2]->id],
                ['place_id' => $libryos[0]->id],
                ['place_id' => $libryos[2]->id],
            ))
            ->typeLibryoActivate()
            ->count(10)
            ->make();
        $date = now();
        foreach ($activities as $ind => $act) {
            $act->forceFill(['created_at' => $date->addMinutes($ind)]);
            $act->save();
        }

        $amount = 5;
        $lastActiveLibryos = app(ActiveLibryosManager::class)->get($user, $amount);
        $this->assertEquals($lastActiveLibryos[0]->id, $libryos[2]->id);
        $this->assertEquals($lastActiveLibryos[1]->id, $libryos[0]->id);
        $this->assertEquals($lastActiveLibryos[2]->id, $libryos[3]->id);
        $this->assertCount($amount, $lastActiveLibryos);
    }

    public function testGetWithoutActivities(): void
    {
        $user = User::factory()->create();
        $libryos = Libryo::factory()->count(10)->create();
        $user->libryos()->attach($libryos);
        $lastActiveLibryos = app(ActiveLibryosManager::class)->get($user, 5);
        $this->assertCount(0, $lastActiveLibryos);
    }

    public function testGetActiveOrganisation(): void
    {
        $org = Organisation::factory()->create();
        $user = User::factory()->create();
        $user->organisations()->attach($org);
        $this->actingAs($user);
        app(ActiveLibryosManager::class)->setMode(LibryoSwitcherMode::all());
        $activeOrg = app(ActiveLibryosManager::class)->getActiveOrganisation($user);
        $this->assertSame($org->id, $activeOrg->id);
    }
}
