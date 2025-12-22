<?php

namespace Tests\Feature\Customer;

use App\Enums\Customer\NormaSwitcherMode;
use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateNorma;
use App\Services\Customer\ActiveNormasManager;
use Tests\Feature\My\MyTestCase;

class NormaControllerTest extends MyTestCase
{
    public function testIndex(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.customer.normas.index';

        $normas = Norma::factory(3)->for($org)->create();

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($norma->title);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($norma->title);
    }

    public function testForMarkers(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.customer.normas.markers.index';

        $response = $this->get(route($routeName, ['bounds' => '90,180,-90,-180', 'zoom' => 1]))->assertSuccessful();
        $response->assertJsonFragment([
            'id' => $norma->id,
            'geo_lat' => (float) $norma->geo_lat,
            'geo_lng' => (float) $norma->geo_lng,
            'quantity' => 1,
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testActivateNorma(): void
    {
        $user = $this->mySuperUser();
        $norma = Norma::factory()->create();

        $route = route('my.normas.activate', ['norma' => $norma->id]);
        $response = $this->actingAs($user)
            ->withExceptionHandling()
            ->post($route);

        // the user has not been assigned to the norma, so they shouldn't be able to activate
        // finding the norma should fail due to global scope
        $response->assertNotFound();

        $norma->users()->attach($user);
        $response = $this->actingAs($user)
            ->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-norma'),
            $norma->id,
        );
        $this->assertTrue($user->activities->isNotEmpty());

        $activeNorma = app(ActiveNormasManager::class)->getActive($user);
        $this->assertEquals($activeNorma->id, $norma->id);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testActivateRedirectNorma(): void
    {
        $user = $this->mySuperUser();
        $norma = Norma::factory()->create();
        $norma->users()->attach($user);

        $route = route('my.normas.activate.redirect', ['norma' => $norma->id, 'redirect' => '/tasks/123']);
        $response = $this->actingAs($user)
            ->get($route);

        $response->assertRedirect('/tasks/123');
        $response->assertSessionHas(
            config('session-keys.customer.active-norma'),
            $norma->id,
        );
    }

    public function testActivateRedirectOrganisation(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $route = route('my.normas.activate.all.redirect', ['organisation' => $org->id, 'redirect' => '/tasks/123']);
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

        $route = route('my.normas.activate.all');
        $response = $this->post($route);

        $response->assertRedirect();
        $response->assertSessionHas(
            config('session-keys.customer.active-norma-mode'),
            NormaSwitcherMode::all()->value,
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

        $norma = Norma::factory()->create(['library_id' => $library->id]);

        $this->assertDatabaseHas(LegalUpdateNorma::class, [
            'register_notification_id' => $update->id,
            'place_id' => $norma->id,
        ]);

        $this->assertDatabaseHas(LegalUpdateNorma::class, [
            'register_notification_id' => $update2->id,
            'place_id' => $norma->id,
        ]);
    }
}
