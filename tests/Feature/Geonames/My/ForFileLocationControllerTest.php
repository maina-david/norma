<?php

namespace Tests\Feature\Geonames\My;

use App\Models\Auth\User;
use App\Models\Geonames\Location;
use App\Models\Storage\My\File;
use Tests\Feature\My\MyTestCase;

class ForFileLocationControllerTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testItRendersTheIndexCorrecly(): void
    {
        $file = File::factory()->create();
        $locations = Location::factory()->count(4)->create();
        $file->locations()->attach($locations->pluck('id')->toArray());

        $route = route('my.locations.for.file.index', ['file' => $file]);

        $user = User::factory()->create();
        $this->signIn($user);

        $this->withExceptionHandling()
            ->get($route)
            ->assertForbidden();

        $this->mySuperUser($user);

        $response = $this->get($route)->assertSuccessful();

        $locations->each(fn ($location) => $response->assertSee($location->title));
    }

    /**
     * @return void
     */
    public function testItAttachesCorrecly(): void
    {
        $file = File::factory()->create();
        $locations = Location::factory()->count(4)->create();

        $route = route('my.locations.for.file.add', ['file' => $file]);

        $user = User::factory()->create();
        $this->signIn($user);

        $this->withExceptionHandling()
            ->post($route)
            ->assertForbidden();

        $this->mySuperUser($user);

        $response = $this->followingRedirects()
            ->post($route, ['locations' => $locations->pluck('id')->toArray()])
            ->assertSuccessful();

        $locations->each(fn ($location) => $response->assertSee($location->title));
    }

    /**
     * @return void
     */
    public function testItDetachesCorrecly(): void
    {
        $file = File::factory()->create();
        $locations = Location::factory()->count(4)->create();
        $file->locations()->attach($locations->pluck('id')->toArray());

        $route = route('my.locations.for.file.actions', ['file' => $file]);

        $remove = $locations->splice(0, 2);
        $payload = $remove->mapWithKeys(fn ($location) => ["actions-checkbox-{$location->id}" => true])->toArray();
        $payload['action'] = 'remove_from_file';

        $user = User::factory()->create();
        $this->signIn($user);

        $this->withExceptionHandling()
            ->post($route)
            ->assertForbidden();

        $this->mySuperUser($user);

        $response = $this->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful();

        $locations->each(fn ($location) => $response->assertSee($location->title));
        $remove->each(fn ($location) => $response->assertDontSee($location->title));
    }
}
