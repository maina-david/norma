<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Compilation\Library;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class ForLibraryLibraryControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $library = Library::factory()->create();
        $org = Organisation::factory()->create();
        Libryo::factory()->for($library)->for($org)->create();
        $route = route('my.settings.children-parents.for.library.index', ['library' => $library->id, 'relation' => 'children']);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $library1 = Library::factory()->create();
        $library2 = Library::factory()->create();
        $library3 = Library::factory()->create();
        $libryo = Libryo::factory()->for($library1)->for($org)->create();

        $library->children()->attach($library1);
        $library->children()->attach($library3);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.children-parents.for.library.index', ['library' => $library->id, 'activateOrgId' => $org->id, 'relation' => 'children']), 'get');

        // when viewing in single organisations mode, you should only see the teams the libryo is part of that are in this org
        $response->assertSee($library1->title);
        $response->assertDontSee($library2->title);
        $response->assertDontSee($library3->title);

        // test the parent route
        $parentRoute = route('my.settings.children-parents.for.library.index', ['library' => $library3->id, 'relation' => 'parents']);
        $response = $this->withActivatedOrg($org)->get($route);

        $response->assertSee($library->title);
    }

    /**
     * @return void
     */
    public function testIndexAllOrgs(): void
    {
        $this->signIn($this->mySuperUser());
        $library = Library::factory()->create();
        $route = route('my.settings.children-parents.for.library.index', ['library' => $library->id, 'relation' => 'children']);

        $library1 = Library::factory()->create();
        $library2 = Library::factory()->create();
        $library3 = Library::factory()->create();
        $library->children()->attach($library1);
        $library->children()->attach($library3);

        $response = $this->withAllOrgMode()->get($route);
        $response->assertSee($library3->title);
    }

    /**
     * @return void
     */
    public function testRemoveChildrenAction(): void
    {
        $org = Organisation::factory()->create();
        $testLibrary = Library::factory()->create();
        $testParent = Library::factory()->create();
        $route = route('my.settings.children-parents.for.library.actions.organisation', ['organisation' => $org->id, 'library' => $testLibrary->id, 'relation' => 'children']);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $library1 = Library::factory()->create();
        $library2 = Library::factory()->create();
        $testLibrary->children()->attach([$library1->id, $library2->id]);
        $testParent->children()->attach($testLibrary);
        $library3 = Library::factory()->create();
        $testLibrary->children()->attach($library3);

        Libryo::factory()->for($library1)->for($org)->create();
        Libryo::factory()->for($library2)->for($org)->create();

        $user->organisations()->attach($org, ['is_admin' => true]);

        // test posting without 'action'
        $response = $this->post($route);
        $response->assertStatus(422);

        // test posting with 'action', but no selected items
        $response = $this->post($route, ['action' => 'remove_children_from_library']);
        $response->assertStatus(422);
        $response->assertSessionHas('flash.message', 'No items selected');

        $this->assertTrue($testLibrary->children->contains($library2));
        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'remove_children_from_library',
            'actions-checkbox-' . $library2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testLibrary->load('children');
        $this->assertFalse($testLibrary->children->contains($library2));
    }

    /**
     * @return void
     */
    public function testRemoveParentsAction(): void
    {
        $org = Organisation::factory()->create();
        $testLibrary = Library::factory()->create();
        $route = route('my.settings.children-parents.for.library.actions.all', ['library' => $testLibrary->id, 'relation' => 'parents']);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $library1 = Library::factory()->create();
        $library2 = Library::factory()->create();
        $testLibrary->parents()->attach([$library1->id, $library2->id]);

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        // test posting without 'action'
        $response = $this->post($route);
        $response->assertStatus(422);

        // test posting with 'action', but no selected items
        $response = $this->post($route, ['action' => 'remove_parents_from_library']);
        $response->assertStatus(422);
        $response->assertSessionHas('flash.message', 'No items selected');

        $this->assertTrue($testLibrary->parents->contains($library2));
        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'remove_parents_from_library',
            'actions-checkbox-' . $library2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testLibrary->load('parents');
        $this->assertFalse($testLibrary->parents->contains($library2));
    }

    /**
     * @return void
     */
    public function testAddLibrariesAction(): void
    {
        $org = Organisation::factory()->create();
        $testLibrary = Library::factory()->create();
        $route = route('my.settings.children-parents.for.library.add', ['library' => $testLibrary->id, 'relation' => 'children']);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $library1 = Library::factory()->create();
        $library2 = Library::factory()->create();
        Libryo::factory()->for($library1)->for($org)->create();

        $response = $this->withActivatedOrg($org)
            ->post($route, [
                'libraries' => [
                    $library1->id,
                    $library2->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testLibrary->children->contains($library1));
        // $user2 is not in the org, so should not get attached in single org mode
        $this->assertFalse($testLibrary->children->contains($library2));

        $route = route('my.settings.children-parents.for.library.add', ['library' => $testLibrary->id, 'relation' => 'parents']);
        $response = $this->withActivatedOrg($org)
            ->post($route, [
                'libraries' => [
                    $library1->id,
                    $library2->id,
                ],
            ]);
        $this->assertTrue($testLibrary->parents->contains($library1));
    }

    /**
     * @return void
     */
    public function testAddLibrariesActionAllOrg(): void
    {
        $testLibrary = Library::factory()->create();
        $route = route('my.settings.children-parents.for.library.add', ['library' => $testLibrary->id, 'relation' => 'children']);
        $libraryNotInOrg = Library::factory()->create();

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);
        $response = $this->withAllOrgMode()
            ->post($route, [
                'libraries' => [
                    $libraryNotInOrg->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testLibrary->children->contains($libraryNotInOrg));
    }

    /**
     * @return void
     */
    public function testAddLibrariesParentsActionAllOrg(): void
    {
        $testLibrary = Library::factory()->create();
        $route = route('my.settings.children-parents.for.library.add', ['library' => $testLibrary->id, 'relation' => 'parents']);
        $libraryNotInOrg = Library::factory()->create();

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);
        $response = $this->withAllOrgMode()
            ->post($route, [
                'libraries' => [
                    $libraryNotInOrg->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testLibrary->parents->contains($libraryNotInOrg));
    }
}
