<?php

namespace Tests\Feature\Auth\Settings;

use App\Enums\Auth\LifecycleStage;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Services\Auth\UserLifecycleService;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\Feature\Settings\SettingsTestCase;

class UserControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.users.index'), 'get');

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $user2 = User::factory()->create();
        // user3 not part of the org
        $user3 = User::factory()->create();
        $user2->organisations()->attach($org);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.users.index', ['activateOrgId' => $org->id]), 'get');

        $response->assertSee($org->title);
        $response->assertSee('users');
        $response->assertSee($user->fname);
        $response->assertSee($user->sname);
        $response->assertSee($user->email);
        $response->assertSee($user2->email);
        $response->assertDontSee($user3->email);

        $response = $this->get(route('my.settings.users.index', ['search' => Str::random(10)]));
        $response->assertDontSee($user->fname);
        $response = $this->get(route('my.settings.users.index', ['search' => $user->fname]));
        $response->assertSee($user->fname);
        $response = $this->get(route('my.settings.users.index', ['search' => substr($user->email, 0, 4)]));
        $response->assertSee($user->fname);
    }

    /**
     * @return void
     */
    public function testIndexAllOrgs(): void
    {
        $user = $this->activateAllOrg();
        $user2 = User::factory()->create();
        $response = $this->get(route('my.settings.users.index'));
        $response->assertSee($user->email);
        $response->assertSee($user2->email);
    }

    public function testIndexFilters(): void
    {
        $user = $this->activateAllOrg();
        $user2 = User::factory()->create();
        $user2->update(['active' => false, 'lifecycle_stage' => LifecycleStage::notOnboarded(), 'fname' => $user2->fname . '_no_duplicate']);
        $response = $this->get(route('my.settings.users.index'));
        $response->assertSee($user->email);
        $response->assertSee($user2->email);
        $response = $this->get(route('my.settings.users.index', ['active' => 1]));
        $response->assertSee($user->email);
        $response->assertDontSee($user2->email);
        $response = $this->get(route('my.settings.users.index', ['active' => 0]));
        $response->assertDontSee($user->email);
        $response = $this->get(route('my.settings.users.index', ['lifecycle_stage' => LifecycleStage::active()->value]));
        $response->assertSee($user->email);
        $response->assertDontSee($user2->email);
        $response = $this->get(route('my.settings.users.index', ['search' => $user->email]));
        $response->assertSee($user->email);
        $response->assertDontSee($user2->email);
        $response = $this->get(route('my.settings.users.index', ['search' => $user->fname]));
        $response->assertSee($user->email);
        $response->assertDontSee($user2->email);
        $response = $this->get(route('my.settings.users.index', ['name' => $user->fname]));
        $response->assertSee($user->email);
        $response->assertDontSee($user2->email);
        $response = $this->get(route('my.settings.users.index', ['email' => $user->email]));
        $response->assertSee($user->email);
        $response->assertDontSee($user2->email);
    }

    public function testIndexJson(): void
    {
        $user = $this->activateAllOrg();

        $users = User::factory(5)->create();
        $users[2]->update(['sname' => $users[2]->sname . '_no_duplicate']);
        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get(route('my.settings.users.index'), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $users[0]->id, 'title' => $users[0]->fullName . ' (' . $users[0]->email . ')']]);
        $response->assertJsonFragment([['id' => $users[1]->id, 'title' => $users[1]->fullName . ' (' . $users[1]->email . ')']]);
        $response->assertJsonFragment([['id' => $users[2]->id, 'title' => $users[2]->fullName . ' (' . $users[2]->email . ')']]);

        $response = $this->get(route('my.settings.users.index', ['search' => $users[0]->sname]), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $users[0]->id, 'title' => $users[0]->fullName . ' (' . $users[0]->email . ')']]);
        $response->assertJsonMissing([['id' => $users[1]->id, 'title' => $users[1]->fullName . ' (' . $users[1]->email . ')']]);

        $response = $this->get(route('my.settings.users.index', ['search' => $users[0]->email]), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $users[0]->id, 'title' => $users[0]->fullName . ' (' . $users[0]->email . ')']]);
        $response->assertJsonMissing([['id' => $users[1]->id, 'title' => $users[1]->fullName . ' (' . $users[1]->email . ')']]);
    }

    /**
     * @return void
     */
    public function testDeactivateAction(): void
    {
        $org = Organisation::factory()->create();
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.users.actions.organisation', ['organisation' => $org->id]), 'post');

        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $user->organisations()->attach($org, ['is_admin' => true]);
        $org->users()->attach($user2);

        $this->get(route('my.settings.users.index', ['activateOrgId' => $org->id]));

        // test posting without 'action'
        $response = $this->post(route('my.settings.users.actions.organisation', ['organisation' => $org->id]));
        $response->assertStatus(422);

        // test posting with 'action', but no selected items
        $response = $this->post(route('my.settings.users.actions.organisation', ['organisation' => $org->id]), ['action' => 'deactivate']);
        $response->assertStatus(422);
        $response->assertSessionHas('flash.message', 'No items selected');

        // test posting with 'action', and with one item
        $response = $this->post(route('my.settings.users.actions.organisation', ['organisation' => $org->id]), [
            'action' => 'deactivate',
            'actions-checkbox-' . $user2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Successfully deactivated selected users');
        $user2 = $user2->fresh();
        $this->assertFalse($user2->active);

        // test trying to deactivate a user that's not in the org
        $response = $this->post(route('my.settings.users.actions.organisation', ['organisation' => $org->id]), [
            'action' => 'deactivate',
            'actions-checkbox-' . $user3->id => 'on',
        ]);
        $response->assertStatus(403);

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $response = $this->post(route('my.settings.users.actions.all'), [
            'action' => 'deactivate',
            'actions-checkbox-' . $user3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Successfully deactivated selected users');
        $user3 = $user3->fresh();
        $this->assertFalse($user3->active);
    }

    /**
     * @return void
     */
    public function testDeactivateAsSuperUserAction(): void
    {
        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $user = User::factory()->create();
        $this->assertTrue($user->active);

        $response = $this->post(route('my.settings.users.actions.all'), [
            'action' => 'deactivate',
            'actions-checkbox-' . $user->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Successfully deactivated selected users');
        $user = $user->fresh();
        $this->assertFalse($user->active);
    }

    /**
     * @return void
     */
    public function testActivateAction(): void
    {
        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $user = User::factory()->create([
            'active' => false,
            'lifecycle_stage' => LifecycleStage::deactivated()->value,
        ]);
        $this->assertFalse($user->active);

        $response = $this->post(route('my.settings.users.actions.all'), [
            'action' => 'activate',
            'actions-checkbox-' . $user->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Successfully activated selected users');
        $user = $user->fresh();
        $this->assertTrue($user->active);
    }

    /**
     * @return void
     */
    public function testMakeRemoveAdminActions(): void
    {
        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $user = User::factory()->create([]);

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);
        $superUser->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->post(route('my.settings.users.actions.organisation', ['organisation' => $org]), [
            'action' => 'make_org_admin',
            'actions-checkbox-' . $user->id => 'on',
        ]);
        $response->assertRedirect()->assertSessionHas('flash.message', 'Successfully made the selected users Administrator');
        $user = $user->fresh();
        $this->assertTrue($user->organisations->first()->pivot->is_admin);

        $response = $this->post(route('my.settings.users.actions.organisation', ['organisation' => $org]), [
            'action' => 'remove_org_admin',
            'actions-checkbox-' . $user->id => 'on',
        ]);
        $response->assertRedirect()->assertSessionHas('flash.message', 'Successfully removed Administrator access');
        $user = $user->fresh();
        $this->assertFalse($user->organisations->first()->pivot->is_admin);

        $response = $this->post(route('my.settings.users.actions.organisation', ['organisation' => $org]), [
            'action' => 'remove_org_admin',
            'actions-checkbox-' . $superUser->id => 'on',
        ]);
        // can't remove admin from themselves
        $this->assertTrue($superUser->refresh()->organisations->where('id', $org->id)->first()->pivot->is_admin);
    }

    /**
     * @return void
     */
    public function testResendWelcomeEmailAction(): void
    {
        $this->mock(UserLifecycleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('reInvite');
        });

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $user = User::factory()->create([
            'active' => false,
            'lifecycle_stage' => LifecycleStage::deactivated()->value,
        ]);

        $response = $this->post(route('my.settings.users.actions.all'), [
            'action' => 'resend_welcome_email',
            'actions-checkbox-' . $user->id => 'on',
        ]);
        $response->assertRedirect();
    }

    /**
     * @return void
     */
    public function testShow(): void
    {
        $user = $this->signIn();
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.users.show', ['user' => $user->hash_id]), 'get', $user);

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $route = route('my.settings.users.show', ['user' => $user->hash_id, 'activateOrgId' => $org->id]);
        $response = $this->assertCanAccessAfterOrgActivate($route, 'get');

        $response->assertSee($user->fullName);
        $response->assertSee('Teams');
        $response->assertSee('Libryo Streams');
    }

    /**
     * @return void
     */
    public function testShowllOrgs(): void
    {
        $user = $this->activateAllOrg();
        $route = route('my.settings.users.show', ['user' => $user->hash_id]);
        $response = $this->get($route);
        $response->assertSuccessful();
    }

    /**
     * @return void
     */
    public function testImpersonate(): void
    {
        $user = $this->activateAllOrg();
        $testUser = User::factory()->create();
        $this->mySuperUser($testUser);

        $route = route('my.settings.users.impersonate', ['user' => $testUser->id]);
        $response = $this->get($route);
        $response->assertRedirect();

        $response->assertSessionHas('impersonating');

        $route = route('my.users.impersonate.leave');
        $response = $this->get($route);

        $response->assertRedirect();

        $response->assertSessionMissing('impersonating');
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $user = $this->signIn();
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.users.create'), 'get', $user);

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $route = route('my.settings.users.create', ['activateOrgId' => $org->id]);
        $response = $this->assertCanAccessAfterOrgActivate($route, 'get');

        $response->assertSee('First Name');
        $response->assertDontSee('User Type');
        $response->assertDontSee('User Roles');

        $this->mySuperUser($user);
        $user->flushComputedPermissions();

        $this->get(route('my.settings.users.create'))
            ->assertSee('User Type')
            ->assertSee('User Roles');
    }

    /**
     * @return void
     */
    public function testStore(): void
    {
        $user = $this->signIn();
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.users.store'), 'post', $user);

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $route = route('my.settings.users.create', ['activateOrgId' => $org->id]);
        $this->assertCanAccessAfterOrgActivate($route, 'get');

        $role = Role::my()->pluck('id')->first();
        $route = route('my.settings.users.store');
        $toCreate = User::factory()->raw();
        $toCreate['user_roles'] = [$role];

        $this->withoutExceptionHandling()
            ->followingRedirects()
            ->from(route('my.settings.users.create'))
            ->post($route, $toCreate)
            ->assertSee($toCreate['fname'])
            ->assertSee('The user has been created and a welcome email has been sent successfully.');

        $created = User::where('email', $toCreate['email'])->firstOrFail();

        $this->assertFalse($created->roles()->where('id', $role)->exists());

        $this->mySuperUser($user);
        $user->flushComputedPermissions();

        $toCreate = User::factory()->raw();
        $toCreate['user_roles'] = [$role];

        $this->withoutExceptionHandling()
            ->followingRedirects()
            ->from(route('my.settings.users.create'))
            ->post($route, $toCreate)
            ->assertSee($toCreate['fname'])
            ->assertSee('The user has been created and a welcome email has been sent successfully.');

        $created = User::where('email', $toCreate['email'])->firstOrFail();
        $this->assertTrue($created->roles()->where('id', $role)->exists());
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        $user = $this->signIn();

        $this->assertForbiddenForNonAdmin(route('my.settings.users.store'), 'post', $user);

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $this->mySuperUser($user);
        $user->flushComputedPermissions();

        $route = route('my.settings.users.create', ['activateOrgId' => $org->id]);
        $this->assertCanAccessAfterOrgActivate($route, 'get');

        $created = User::factory()->create(['fname' => 'Uniquefirstname']);
        $newRole = Role::factory()->my()->create();
        $update = $created->toArray();
        $update['fname'] = 'First Name';
        $update['user_roles'] = Role::my()->pluck('id')->toArray();

        $this->withoutExceptionHandling()
            ->followingRedirects()
            ->from(route('my.settings.users.update', [$created]))
            ->put(route('my.settings.users.update', [$created]), $update)
            ->assertSee($update['fname'])
            ->assertDontSee($created->fname);
    }

    /**
     * @return void
     **/
    public function testSortingUsersByStatus(): void
    {
        $this->signIn($this->mySuperUser());

        $active = User::factory()->create(['active' => true]);
        $inActive = User::factory()->create(['active' => false]);

        // Test filtering by 'active' status
        $this->get(route('my.settings.users.index', ['active' => 1]))
            ->assertSuccessful()
            ->assertSee($active->fname)
            ->assertDontSee($inActive->fname);

        $this->get(route('my.settings.users.index', ['active' => 0]))
            ->assertSuccessful()
            ->assertSee($inActive->fname)
            ->assertDontSee($active->fname);
    }

    /**
     * @return void
     **/
    public function testAnonymizeAndDestroyAccount(): void
    {
        $this->signIn($this->mySuperUser());

        $user = User::factory()->create();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $this->post(route('my.settings.users.actions.all'), [
            'action' => 'delete_account',
            'actions-checkbox-' . $user->id => 'on',
        ])->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->deleted_at);
        $this->assertEquals('Deleted', $user->fname);
        $this->assertEquals('User', $user->sname);
        $this->assertEquals('deleted' . $user->id . '@libryo.com', $user->email);
    }
}
