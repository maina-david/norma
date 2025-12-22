<?php

namespace Tests\Feature\Customer;

use App\Enums\Customer\NormaSwitcherMode;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use Tests\Feature\My\MyTestCase;

class NormaSwitcherTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testNormaSwitcherNoNormas(): void
    {
        $user = $this->signIn($this->mySuperUser());
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);

        $route = route('my.dashboard');
        $response = $this->actingAs($user)->get($route);

        $mode = app(ActiveNormasManager::class)->getMode();
        $this->assertTrue(NormaSwitcherMode::all()->is($mode));
    }

    /**
     * @return void
     */
    public function testNormaSwitcherSingle(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $route = route('my.normas.activate', ['norma' => $norma->id]);
        $response = $this->actingAs($user)->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-norma'),
            $norma->id,
        );

        $response = $this->get(route('my.dashboard'));
        $response->assertSuccessful();

        $response->assertSeeText($norma->title);
    }

    /**
     * @return void
     */
    public function testNormaSwitcherMulti(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $norma2 = Norma::factory()->for($org)->create();
        $user->normas()->attach($norma2);
        $route = route('my.normas.activate', ['norma' => $norma2->id]);

        $response = $this->actingAs($user)->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-norma'),
            $norma2->id,
        );

        $response = $this->get(route('my.dashboard'));
        $response->assertSuccessful();

        $response->assertSeeText($norma2->title);
        $response->assertSeeText('Switch to another Norma Stream');
        $response->assertSeeText('Recently Viewed');
    }

    public function testNormaSwitcherRecent(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $norma2 = Norma::factory()->create();
        $norma2->users()->attach($user);
        $this->signIn($user);
        app(ActiveNormasManager::class)->activate($user, $norma);
        app(ActiveNormasManager::class)->activate($user, $norma2);

        $route = route('my.normas.switcher.recent');
        $response = $this->get($route);

        $response->assertSeeText($norma->title);
        $response->assertSeeText($norma2->title);
    }
}
