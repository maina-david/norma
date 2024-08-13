<?php

namespace Tests\Feature\Customer;

use App\Models\Customer\Libryo;
use App\Models\Customer\Team;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\My\MyTestCase;

class LibryoStreamControllerTest extends MyTestCase
{
    public function testApplicableForLegalUpdate(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $update = LegalUpdate::factory()->create();
        $libryo->legalUpdates()->attach($update);
        $route = route('my.notify.legal-updates.libryos.index', ['update' => $update]);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($libryo->title);
    }

    public function testSwitcherSearch(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $team = Team::factory()->create();
        $libryo2 = Libryo::factory()->create();
        $libryo3 = Libryo::factory()->create();
        $team->libryos()->attach($libryo2->id);
        $user->teams()->attach($team);
        $user->libryos()->attach($libryo2, ['via_teams' => true]);

        $route = route('my.libryos.switcher.search');

        $response = $this->post($route)->assertSuccessful();
        $response->assertSee($libryo->title);
        $response->assertSee($libryo2->title);
        $response->assertDontSee($libryo3->title);
    }
}
