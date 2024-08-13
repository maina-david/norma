<?php

namespace Tests\Feature\Customer;

use App\Enums\Customer\LibryoSwitcherMode;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Tests\Feature\My\MyTestCase;

class LibryoSwitcherTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testLibryoSwitcherNoLibryos(): void
    {
        $user = $this->signIn($this->mySuperUser());
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);

        $route = route('my.dashboard');
        $response = $this->actingAs($user)->get($route);

        $mode = app(ActiveLibryosManager::class)->getMode();
        $this->assertTrue(LibryoSwitcherMode::all()->is($mode));
    }

    /**
     * @return void
     */
    public function testLibryoSwitcherSingle(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $route = route('my.libryos.activate', ['libryo' => $libryo->id]);
        $response = $this->actingAs($user)->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-libryo'),
            $libryo->id,
        );

        $response = $this->get(route('my.dashboard'));
        $response->assertSuccessful();

        $response->assertSeeText($libryo->title);
    }

    /**
     * @return void
     */
    public function testLibryoSwitcherMulti(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $libryo2 = Libryo::factory()->for($org)->create();
        $user->libryos()->attach($libryo2);
        $route = route('my.libryos.activate', ['libryo' => $libryo2->id]);

        $response = $this->actingAs($user)->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-libryo'),
            $libryo2->id,
        );

        $response = $this->get(route('my.dashboard'));
        $response->assertSuccessful();

        $response->assertSeeText($libryo2->title);
        $response->assertSeeText('Switch to another Libryo Stream');
        $response->assertSeeText('Recently Viewed');
    }

    public function testLibryoSwitcherRecent(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo2 = Libryo::factory()->create();
        $libryo2->users()->attach($user);
        $this->signIn($user);
        app(ActiveLibryosManager::class)->activate($user, $libryo);
        app(ActiveLibryosManager::class)->activate($user, $libryo2);

        $route = route('my.libryos.switcher.recent');
        $response = $this->get($route);

        $response->assertSeeText($libryo->title);
        $response->assertSeeText($libryo2->title);
    }
}
