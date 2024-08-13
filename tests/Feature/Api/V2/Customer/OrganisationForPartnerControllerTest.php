<?php

namespace Tests\Feature\Api\V2\Customer;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\OrganisationUser;
use App\Models\Partners\Partner;
use App\Models\Partners\WhiteLabel;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OrganisationForPartnerControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItAuthorisesCorrectly(): void
    {
        $this->assertGuest();

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.partners.organisations.store'))
            ->assertUnauthorized();

        $partner = Partner::factory()->create();
        $user = User::factory()->create();
        $role = Role::factory()->my()->create();
        $user->roles()->attach($role->id);

        Passport::actingAs($user);

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.partners.organisations.store'))
            ->assertUnprocessable();

        $payload = [
            'title' => 'Test Organisation',
            'partner_id' => $partner->id,
        ];

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.partners.organisations.store'), $payload)
            ->assertForbidden();

        $role = Role::factory()->my()->create(['title' => 'Integration User']);
        $user->roles()->attach($role->id);

        $this->withExceptionHandling()
            ->json('POST', route('api.v2.hq.partners.organisations.store'), $payload)
            ->assertForbidden();

        $user->partner_id = $partner->id;
        $user->save();

        $this->withoutExceptionHandling()
            ->json('POST', route('api.v2.hq.partners.organisations.store'), $payload)
            ->assertSuccessful();
    }

    /**
     * @return void
     */
    public function testItCreatesTheOrganisation(): void
    {
        $wl = WhiteLabel::factory()->create();
        $partner = Partner::factory()->create();
        $user = User::factory()->create(['partner_id' => $partner->id]);
        $role = Role::factory()->my()->create(['title' => 'Integration User']);
        $user->roles()->attach($role->id);

        Passport::actingAs($user);

        $payload = [
            'title' => 'Valid Organisation',
            'partner_id' => $partner->id,
            'whitelabel_id' => $wl->id,
            'integration_id' => 'test',
        ];

        $this->assertDatabaseMissing((new OrganisationUser())->getTable(), [
            'user_id' => $user->id,
            'is_admin' => true,
        ]);

        unset($payload['whitelabel_id']);

        $this->withoutExceptionHandling()
            ->json('POST', route('api.v2.hq.partners.organisations.store'), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => $payload,
                'meta' => ['message' => 'Saved successfully'],
            ]);

        $this->assertDatabaseHas((new OrganisationUser())->getTable(), [
            'user_id' => $user->id,
            'is_admin' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testItUpdatesTheOrganisation(): void
    {
        $partner = Partner::factory()->create();
        $organisation = Organisation::factory()->create(['partner_id' => $partner->id, 'integration_id' => 'test']);
        $user = User::factory()->create(['partner_id' => $partner->id]);
        $role = Role::factory()->my()->create(['title' => 'Integration User']);
        $user->roles()->attach($role->id);
        $user->organisations()->attach($organisation->id, ['is_admin' => true]);

        Passport::actingAs($user);

        $payload = [
            'title' => 'Valid Organisation',
        ];

        $this->assertDatabaseMissing((new Organisation())->getTable(), ['title' => 'Valid Organisation']);

        $this->withoutExceptionHandling()
            ->json('PUT', route('api.v2.hq.partners.organisations.update', ['organisation' => $organisation]), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => $organisation->id,
                    'title' => 'Valid Organisation',
                    'partner_id' => $partner->id,
                    'integration_id' => 'test',
                ],
                'meta' => ['message' => 'Saved successfully'],
            ]);

        $this->assertDatabaseHas((new Organisation())->getTable(), ['title' => 'Valid Organisation']);
    }
}
