<?php

namespace Tests\Feature\Dashboard\My\Settings;

use Tests\Feature\Settings\SettingsTestCase;

class DashboardControllerTest extends SettingsTestCase
{
    public function testDashboard(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.settings.dashboard';

        $response = $this->followingRedirects()->get(route($routeName, ['activateOrgId' => $org->id]));

        $response->assertSuccessful();
        $response->assertSee('Welcome back');
        $response->assertSee('Active Users');
        $response->assertSee('Teams');

        $this->activateAllOrg($user);

        $response = $this->followingRedirects()->get(route($routeName));

        $response->assertSee('Welcome back');
        $response->assertSee('Active Users');
    }
}
