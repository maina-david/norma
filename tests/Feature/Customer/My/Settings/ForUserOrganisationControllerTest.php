<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\OrganisationUser;
use Tests\Feature\Settings\SettingsTestCase;

class ForUserOrganisationControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIsRendersScopedAndFullIndex(): void
    {
        $user = $this->signIn();

        $modify = User::factory()->create();
        $route = route('my.settings.organisations.for.user.index', ['user' => $modify->id]);

        $user = $this->assertForbiddenForNonAdmin($route, 'get', $user);

        $unattached = Organisation::factory()->create(['title' => 'Not Your Organisation']);
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $modify->organisations()->attach([$org->id, $unattached->id]);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee($org->title)
            ->assertDontSee($unattached->title)
            ->assertDontSeeSelector('#select-all-checkbox');

        $this->mySuperUser($user);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee($org->title)
            ->assertSee($unattached->title)
            ->assertSeeSelector('#select-all-checkbox');
    }

    /**
     * @return void
     */
    public function testItAttachesAndDetachesOrganisations(): void
    {
        $user = $this->signIn();
        $modify = User::factory()->create();
        $route = route('my.settings.organisations.for.user.add', ['user' => $modify->id]);

        $user = $this->assertForbiddenForNonAdmin($route, 'post', $user);

        $unaccess = Organisation::factory()->create(['title' => 'Not Your Organisation']);
        $access = Organisation::factory()->create(['title' => 'In Your Organisation']);

        $org = Organisation::factory()->create();
        $user->organisations()->attach([$org->id, $access->id], ['is_admin' => true]);

        $this->json('get', route('my.settings.organisations.for.user.search'))
            ->assertSuccessful()
            ->assertJson([
                ['id' => $access->id],
                ['id' => $org->id],
            ])
            ->assertJsonMissing([
                ['id' => $unaccess->id],
            ]);

        $toAdd = $access->id;

        $this->assertDatabaseMissing(new OrganisationUser(), ['user_id' => $modify->id, 'organisation_id' => $toAdd]);

        $this->withoutExceptionHandling()
            ->followingRedirects()
            ->post($route, ['organisations' => [$toAdd]])
            ->assertSuccessful()
            ->assertSee($access->title);

        $this->assertDatabaseHas(new OrganisationUser(), ['user_id' => $modify->id, 'organisation_id' => $toAdd]);

        $route = route('my.settings.organisations.for.user.actions', ['user' => $modify->id]);

        $this->withoutExceptionHandling()
            ->followingRedirects()
            ->post($route, ['action' => 'remove_from_user', "actions-checkbox-{$toAdd}" => true])
            ->assertSuccessful()
            ->assertDontSee($access->title);

        $this->assertDatabaseMissing(new OrganisationUser(), ['user_id' => $modify->id, 'organisation_id' => $toAdd]);
    }
}
