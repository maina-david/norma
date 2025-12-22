<?php

namespace Tests\Unit\Services\Customer;

use App\Enums\Customer\NormaSwitcherMode;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class ActiveNormasManagerTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet(): void
    {
        $user = User::factory()->create();
        $normas = Norma::factory()->count(10)->create();
        $user->normas()->attach($normas->modelKeys());

        $activities = UserActivity::factory()
            ->for($user)
            ->state(new Sequence(
                ['place_id' => $normas[2]->id],
                ['place_id' => $normas[4]->id],
                ['place_id' => $normas[3]->id],
                ['place_id' => $normas[1]->id],
                ['place_id' => $normas[3]->id],
                ['place_id' => $normas[0]->id],
                ['place_id' => $normas[2]->id],
                ['place_id' => $normas[0]->id],
                ['place_id' => $normas[2]->id],
            ))
            ->typeNormaActivate()
            ->count(10)
            ->make();
        $date = now();
        foreach ($activities as $ind => $act) {
            $act->forceFill(['created_at' => $date->addMinutes($ind)]);
            $act->save();
        }

        $amount = 5;
        $lastActiveNormas = app(ActiveNormasManager::class)->get($user, $amount);
        $this->assertEquals($lastActiveNormas[0]->id, $normas[2]->id);
        $this->assertEquals($lastActiveNormas[1]->id, $normas[0]->id);
        $this->assertEquals($lastActiveNormas[2]->id, $normas[3]->id);
        $this->assertCount($amount, $lastActiveNormas);
    }

    public function testGetWithoutActivities(): void
    {
        $user = User::factory()->create();
        $normas = Norma::factory()->count(10)->create();
        $user->normas()->attach($normas);
        $lastActiveNormas = app(ActiveNormasManager::class)->get($user, 5);
        $this->assertCount(0, $lastActiveNormas);
    }

    public function testGetActiveOrganisation(): void
    {
        $org = Organisation::factory()->create();
        $user = User::factory()->create();
        $user->organisations()->attach($org);
        $this->actingAs($user);
        app(ActiveNormasManager::class)->setMode(NormaSwitcherMode::all());
        $activeOrg = app(ActiveNormasManager::class)->getActiveOrganisation($user);
        $this->assertSame($org->id, $activeOrg->id);
    }
}
