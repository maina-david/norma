<?php

namespace Tests\Feature\Api\V2\Customer;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\LegalDomainLibryo;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Partners\Partner;
use Laravel\Passport\Passport;
use Tests\TestCase;

class LibryoForOrganisationControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItAuthorisesCorrectly(): void
    {
        $organisation = Organisation::factory()->create();

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.organisations.libryos.store', ['organisation' => $organisation]))
            ->assertUnauthorized();

        $user = User::factory()->create();
        $role = Role::factory()->my()->create();
        $user->roles()->attach($role->id);

        Passport::actingAs($user);

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.organisations.libryos.store', ['organisation' => $organisation]))
            ->assertForbidden();

        $user->organisations()->attach($organisation);

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.organisations.libryos.store', ['organisation' => $organisation]))
            ->assertForbidden();

        $user->organisations()->updateExistingPivot($organisation, ['is_admin' => true]);

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.organisations.libryos.store', ['organisation' => $organisation]))
            ->assertUnprocessable();
    }

    /**
     * @return void
     */
    public function testItCreatesAndUpdatesTheLibryo(): void
    {
        $partner = Partner::factory()->create();
        $organisation = Organisation::factory()->create(['partner_id' => $partner->id]);
        $user = User::factory()->create();
        $role = Role::factory()->my()->create();
        $user->roles()->attach($role->id);
        $user->organisations()->attach($organisation, ['is_admin' => true]);
        $location = Location::factory()->create();
        $domains = LegalDomain::factory()->count(3)->create();

        $data = [
            'integration_id' => $this->faker->word(),
            'address' => $this->faker->streetAddress(),
            'location_id' => $location->id,
            'title' => $this->faker->words(3, true),
            'geo_lat' => $this->faker->latitude(),
            'geo_lng' => $this->faker->longitude(),
            'legalDomains' => $domains->pluck('id')->toArray(),
        ];

        Passport::actingAs($user);

        $this->withoutExceptionHandling()
            ->json('POST', route('api.v2.hq.organisations.libryos.store', ['organisation' => $organisation]), $data)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'title' => $data['title'],
                    'geo_lat' => (string) $data['geo_lat'],
                    'geo_lng' => (string) $data['geo_lng'],
                    'address' => $data['address'],
                    'location_id' => $data['location_id'],
                    'organisation_id' => $organisation->id,
                    'deactivated' => false,
                    'description' => null,
                    'compilation_in_progress' => false,
                ],
                'meta' => [
                    'message' => 'Saved successfully',
                ],
            ]);

        unset($data['legalDomains']);

        $this->assertDatabaseHas((new Libryo())->getTable(), $data);

        $libryo = Libryo::where($data)->firstOrFail();

        $table = (new LegalDomainLibryo())->getTable();

        $domains->each(fn ($dom) => $this->assertDatabaseHas($table, [
            'legal_domain_id' => $dom->id,
            'place_id' => $libryo->id,
        ]));

        $this->assertNotNull($libryo->library_id);

        $unassigned = Libryo::factory()->create();

        $this->withExceptionHandling()
            ->json('PUT', route('api.v2.hq.organisations.libryos.update', ['organisation' => $organisation, 'libryo' => $unassigned]), [])
            ->assertNotFound();

        $update = ['title' => $this->faker->words(4, true)];

        $this->withoutExceptionHandling()
            ->json('PUT', route('api.v2.hq.organisations.libryos.update', ['organisation' => $organisation, 'libryo' => $libryo]), $update)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'title' => $update['title'],
                    'geo_lat' => (string) $data['geo_lat'],
                    'geo_lng' => (string) $data['geo_lng'],
                    'address' => $data['address'],
                    'location_id' => $data['location_id'],
                    'organisation_id' => $organisation->id,
                    'deactivated' => false,
                    'description' => null,
                    'compilation_in_progress' => false,
                ],
                'meta' => [
                    'message' => 'Saved successfully',
                ],
            ]);
    }
}
