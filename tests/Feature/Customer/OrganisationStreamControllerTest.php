<?php

namespace Tests\Feature\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Tests\Feature\My\MyTestCase;

class OrganisationStreamControllerTest extends MyTestCase
{
    public function testSwitcherSearch(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $org2 = Organisation::factory()->create();

        $route = route('my.settings.organisations.switcher.search');

        $response = $this->post($route, ['search_organisations' => $org->title])->assertSuccessful();
        $response->assertSee($org->title);
        $response->assertDontSee($org2->title);

        $user2 = User::factory()->create();
        $user2->organisations()->attach($org, ['is_admin' => true]);
        $this->signIn($user2);
        $response = $this->post($route, ['search_organisations' => $org->title])->assertSuccessful();
        $response->assertSee($org->title);
    }
}
