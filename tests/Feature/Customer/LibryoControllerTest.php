<?php

namespace Tests\Feature\Customer;

use App\Enums\Customer\LibryoSwitcherMode;
use App\Models\Compilation\Library;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateLibryo;
use App\Services\Customer\ActiveLibryosManager;
use Tests\Feature\My\MyTestCase;

class LibryoControllerTest extends MyTestCase
{
    public function testIndex(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.customer.libryos.index';

        $libryos = Libryo::factory(3)->for($org)->create();

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($libryo->title);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($libryo->title);
    }

    public function testForMarkers(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.customer.libryos.markers.index';

        $response = $this->get(route($routeName, ['bounds' => '90,180,-90,-180', 'zoom' => 1]))->assertSuccessful();
        $response->assertJsonFragment([
            'id' => $libryo->id,
            'geo_lat' => (float) $libryo->geo_lat,
            'geo_lng' => (float) $libryo->geo_lng,
            'quantity' => 1,
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testActivateLibryo(): void
    {
        $user = $this->mySuperUser();
        $libryo = Libryo::factory()->create();

        $route = route('my.libryos.activate', ['libryo' => $libryo->id]);
        $response = $this->actingAs($user)
            ->withExceptionHandling()
            ->post($route);

        // the user has not been assigned to the libryo, so they shouldn't be able to activate
        // finding the libryo should fail due to global scope
        $response->assertNotFound();

        $libryo->users()->attach($user);
        $response = $this->actingAs($user)
            ->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-libryo'),
            $libryo->id,
        );
        $this->assertTrue($user->activities->isNotEmpty());

        $activeLibryo = app(ActiveLibryosManager::class)->getActive($user);
        $this->assertEquals($activeLibryo->id, $libryo->id);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testActivateRedirectLibryo(): void
    {
        $user = $this->mySuperUser();
        $libryo = Libryo::factory()->create();
        $libryo->users()->attach($user);

        $route = route('my.libryos.activate.redirect', ['libryo' => $libryo->id, 'redirect' => '/tasks/123']);
        $response = $this->actingAs($user)
            ->get($route);

        $response->assertRedirect('/tasks/123');
        $response->assertSessionHas(
            config('session-keys.customer.active-libryo'),
            $libryo->id,
        );
    }

    public function testActivateRedirectOrganisation(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $route = route('my.libryos.activate.all.redirect', ['organisation' => $org->id, 'redirect' => '/tasks/123']);
        $response = $this->actingAs($user)
            ->get($route);

        $response->assertRedirect('/tasks/123');
        $response->assertSessionHas(
            config('session-keys.customer.active-organisation'),
            $org->id,
        );
    }

    public function testActivateAll(): void
    {
        $user = $this->mySuperUser();
        $this->signIn($user);

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);

        $route = route('my.libryos.activate.all');
        $response = $this->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-libryo-mode'),
            LibryoSwitcherMode::all()->value,
        );
    }

    public function testInheritanceOfUpdates(): void
    {
        $library = Library::factory()->create();
        $parent = Library::factory()->create();
        $library->parents()->attach($parent->id);
        $update = LegalUpdate::factory()->create();
        $update2 = LegalUpdate::factory()->create();

        $library->legalUpdates()->attach($update->id);
        $parent->legalUpdates()->attach($update2->id);

        $libryo = Libryo::factory()->create(['library_id' => $library->id]);

        $this->assertDatabaseHas(LegalUpdateLibryo::class, [
            'register_notification_id' => $update->id,
            'place_id' => $libryo->id,
        ]);

        $this->assertDatabaseHas(LegalUpdateLibryo::class, [
            'register_notification_id' => $update2->id,
            'place_id' => $libryo->id,
        ]);
    }
}
