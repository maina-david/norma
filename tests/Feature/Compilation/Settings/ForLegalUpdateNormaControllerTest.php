<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\Settings\SettingsTestCase;

class ForLegalUpdateNormaControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItRendersCorrectItems(): void
    {
        $user = $this->signIn();
        $update = LegalUpdate::factory()->create();
        $route = route('my.settings.compilation.normas.for.legal-update.index', ['update' => $update->id]);

        $unattached = Norma::factory()->count(3)->create();
        $attached = Norma::factory()->count(3)->create();

        $update->normas()->attach($attached->pluck('id')->toArray());

        $this->assertForbiddenForNonAdmin($route, 'get', $user);

        $this->mySuperUser($user);

        $response = $this->get($route)->assertSuccessful();

        $unattached->each(fn ($norma) => $response->assertDontSee($norma->title));
        $attached->each(fn ($norma) => $response->assertSee($norma->title));
    }
}
