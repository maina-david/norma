<?php

namespace Tests\Feature\Log\Settings;

use App\Enums\Auth\LifecycleStage;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Log\UserLifecycleActivity;
use Tests\Feature\Settings\SettingsTestCase;

class UserLifecycleActivitiesControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testForUser(): void
    {
        $user = $this->signIn();
        $testUser = User::factory()->create();
        $org = Organisation::factory()->create();
        $route = route('my.settings.users.lifecycle.activities', ['user' => $testUser->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $activity = UserLifecycleActivity::factory()->for($testUser)->create();

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.users.lifecycle.activities', ['user' => $testUser->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the libryo is part of that are in this org
        $response->assertSee(LifecycleStage::lang()[$activity->to_lifecycle]);
        $response->assertSee(LifecycleStage::lang()[$activity->from_lifecycle]);
    }
}
