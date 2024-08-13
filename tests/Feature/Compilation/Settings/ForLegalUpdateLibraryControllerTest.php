<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Compilation\Library;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\Settings\SettingsTestCase;

class ForLegalUpdateLibraryControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItRendersCorrectItems(): void
    {
        $user = $this->signIn();
        $update = LegalUpdate::factory()->create();
        $route = route('my.settings.compilation.library.for.legal-update.index', ['update' => $update->id]);

        $unattached = Library::factory()->count(3)->create();
        $attached = Library::factory()->count(3)->create();

        $update->libraries()->attach($attached->pluck('id')->toArray());

        $this->assertForbiddenForNonAdmin($route, 'get', $user);

        $this->mySuperUser($user);

        $response = $this->get($route)->assertSuccessful();

        $unattached->each(fn ($library) => $response->assertDontSee($library->title));
        $attached->each(fn ($library) => $response->assertSee($library->title));
    }
}
