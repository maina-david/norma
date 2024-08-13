<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Enums\Auth\LifecycleStage;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Services\Auth\UserLifecycleService;
use Mockery\MockInterface;
use Tests\Feature\Settings\SettingsTestCase;

class ForOrganisationUserControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $org = Organisation::factory()->create();
        $user = $this->signIn();
        $route = route('my.settings.users.for.organisation.index', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $this->activateAllOrg();

        $aUser = User::factory()->create();
        $aUser->organisations()->attach($org);

        $response = $this->get($route);
        $response->assertSee($aUser->fullName);
    }

    /**
     * @return void
     */
    public function testRemoveUserFromOrganisationrAction(): void
    {
        $org = Organisation::factory()->create();
        $testUser = User::factory()->create();
        $route = route('my.settings.users.for.organisation.actions.all', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $testUser->organisations()->attach($org);

        $this->activateAllOrg();

        // test posting with 'action', and with one item
        $response = $this->post($route, [
            'action' => 'remove_from_organisation',
            'actions-checkbox-' . $testUser->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testUser->load('organisations');
        $this->assertFalse($testUser->organisations->contains($org));
    }

    /**
     * @return void
     */
    public function testAddUsersAction(): void
    {
        $org = Organisation::factory()->create();
        $testUser = User::factory()->create();
        $route = route('my.settings.users.for.organisation.add', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $this->activateAllOrg();

        $response = $this->post($route, [
            'users' => [
                $testUser->id,
            ],
        ]);
        $response->assertRedirect();
        $this->assertTrue($testUser->organisations->contains($org));
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

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);

        $response = $this->post(route('my.settings.users.for.organisation.actions.all', ['organisation' => $org]), [
            'action' => 'resend_welcome_email',
            'actions-checkbox-' . $user->id => 'on',
        ]);
        $response->assertRedirect();
    }

    /**
     * @return void
     */
    public function testDeleteUserAccount(): void
    {
        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $user = User::factory()->create();

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);

        $this->post(route('my.settings.users.for.organisation.actions.all', ['organisation' => $org]), [
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
