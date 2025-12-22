<?php

namespace Tests\Feature\Customer;

use App\Models\Customer\Norma;
use App\Models\Customer\Team;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\My\MyTestCase;

class NormaStreamControllerTest extends MyTestCase
{
    public function testApplicableForLegalUpdate(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $update = LegalUpdate::factory()->create();
        $norma->legalUpdates()->attach($update);
        $route = route('my.notify.legal-updates.normas.index', ['update' => $update]);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($norma->title);
    }

    public function testSwitcherSearch(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $team = Team::factory()->create();
        $norma2 = Norma::factory()->create();
        $norma3 = Norma::factory()->create();
        $team->normas()->attach($norma2->id);
        $user->teams()->attach($team);
        $user->normas()->attach($norma2, ['via_teams' => true]);

        $route = route('my.normas.switcher.search');

        $response = $this->post($route)->assertSuccessful();
        $response->assertSee($norma->title);
        $response->assertSee($norma2->title);
        $response->assertDontSee($norma3->title);
    }
}
