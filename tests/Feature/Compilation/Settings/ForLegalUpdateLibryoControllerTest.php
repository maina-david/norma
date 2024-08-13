<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Customer\Libryo;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\Settings\SettingsTestCase;

class ForLegalUpdateLibryoControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItRendersCorrectItems(): void
    {
        $user = $this->signIn();
        $update = LegalUpdate::factory()->create();
        $route = route('my.settings.compilation.libryos.for.legal-update.index', ['update' => $update->id]);

        $unattached = Libryo::factory()->count(3)->create();
        $attached = Libryo::factory()->count(3)->create();

        $update->libryos()->attach($attached->pluck('id')->toArray());

        $this->assertForbiddenForNonAdmin($route, 'get', $user);

        $this->mySuperUser($user);

        $response = $this->get($route)->assertSuccessful();

        $unattached->each(fn ($libryo) => $response->assertDontSee($libryo->title));
        $attached->each(fn ($libryo) => $response->assertSee($libryo->title));
    }
}
