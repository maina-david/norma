<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class ForOrganisationChildOrganisationControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $org = Organisation::factory()->create(['is_parent' => true]);
        $child = Organisation::factory()->create(['parent_id' => $org->id]);
        $route = route('my.settings.child-organisations.for.organisation.index', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $this->mySuperUser($user);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee($child->title);
    }

    public function testListingAndAdding(): void
    {
        $org = Organisation::factory()->create(['is_parent' => true]);
        $child = Organisation::factory()->create(['parent_id' => null, 'is_parent' => false]);

        $route = route('my.settings.child-organisations.for.organisation.store', $org->id);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $this->mySuperUser($user);

        $this->json('GET', route('my.settings.organisations.index'), ['search' => substr($org->title, 0, 5), 'parent' => 1])
            ->assertSuccessful()
            ->assertJson([['id' => $org->id]]);

        $this->json('GET', route('my.settings.organisations.children.search'), ['search' => substr($child->title, 0, 5)])
            ->assertSuccessful()
            ->assertJson([['id' => $child->id]]);

        $this->followingRedirects()
            ->post(route('my.settings.child-organisations.for.organisation.store', ['organisation' => $org->id]), ['organisations' => [$child->id]])
            ->assertSuccessful()
            ->assertSessionHasNoErrors()
            ->assertSee($child->title);
    }

    public function testRemoval(): void
    {
        $org = Organisation::factory()->create(['is_parent' => true]);
        $child = Organisation::factory()->create(['parent_id' => $org->id]);
        $route = route('my.settings.child-organisations.for.organisation.actions', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $this->mySuperUser($user);

        $this->get(route('my.settings.child-organisations.for.organisation.index', ['organisation' => $org->id]))
            ->assertSuccessful()
            ->assertSee($child->title);

        $this->followingRedirects()
            ->post($route, ['action' => 'remove_from_organisation', "actions-checkbox-{$child->id}" => true])
            ->assertSuccessful()
            ->assertSessionHasNoErrors()
            ->assertDontSee($child->title);
    }
}
