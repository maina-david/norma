<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Enums\Customer\OrganisationLevel;
use App\Enums\Customer\OrganisationPlan;
use App\Enums\Customer\OrganisationType;
use App\Jobs\Auth\CRMSyncOrganisationUsers;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Partners\Partner;
use App\Models\Partners\WhiteLabel;
use Config;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Settings\SettingsTestCase;

class OrganisationControllerTest extends SettingsTestCase
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Organisation::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'my.settings.organisations';
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function editFormVisibleLabels(): array
    {
        return ['Title', 'Plan', 'Type', 'Customer Categorisation', 'Whitelabel', 'Translation Services', 'Parent Organisation'];
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function editFormVisibleFields(): array
    {
        return ['title'];
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function createFormVisibleLabels(): array
    {
        return ['Title', 'Plan', 'Type', 'Customer Categorisation', 'Whitelabel', 'Translation Services', 'Parent Organisation'];
    }

    /**
     * @return void
     */
    public function testIndexForbiddenForNonSuperAdmin(): void
    {
        $route = route('my.settings.organisations.index');
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withExceptionHandling()->get($route);
        $response->assertForbidden();
    }

    public function testIndexAllOrgs(): void
    {
        $user = $this->activateAllOrg();

        $organisation = Organisation::factory()->create();
        $partner = Partner::factory()->create();
        $organisation2 = Organisation::factory()->create(['integration_id' => '123', 'partner_id' => $partner->id]);

        $orgs = Organisation::factory(75)->create();

        $response = $this->get(route('my.settings.organisations.index'));
        $response->assertSuccessful();
        $response->assertSee('Organisations');

        $response->assertSee($organisation->title);
        $response->assertSee($organisation2->title);
        $response->assertSeeSelector('//table//tr//*[contains(text(),"' . $organisation->title . '")]');
        $response->assertSeeSelector('//table//tr//*[contains(text(),"' . $organisation2->title . '")]');

        $response = $this->get(route('my.settings.organisations.index', ['page' => 2]));
        $response->assertSuccessful();
        $response->assertSee($orgs->last()->title);
        $response = $this->get(route('my.settings.organisations.index', ['search' => $organisation->title]));
        $response->assertSuccessful();
        $response->assertSee($organisation->title);
        $response->assertDontSee($organisation2->title);

        // test filtering organisations
        $organisation3 = Organisation::factory()->create(['customer_category' => OrganisationLevel::b()->value]);
        $response = $this->get(route('my.settings.organisations.index', ['customer_category' => OrganisationLevel::b()->value]));
        $response->assertSee($organisation3->title);
        $response->assertDontSee($organisation->title);

        $organisation4 = Organisation::factory()->create(['plan' => OrganisationPlan::free()->value]);
        $response = $this->get(route('my.settings.organisations.index', ['plan' => OrganisationPlan::free()->value]));
        $response->assertSee($organisation4->title);
        $response->assertDontSee($organisation->title);

        $organisation5 = Organisation::factory()->create(['type' => OrganisationType::smb()->value]);
        $response = $this->get(route('my.settings.organisations.index', ['type' => OrganisationType::smb()->value]));
        $response->assertSee($organisation5->title);
        $response->assertDontSee($organisation->title);

        $whitelabel = WhiteLabel::factory()->create();
        $organisation6 = Organisation::factory()->create(['whitelabel_id' => $whitelabel->id]);
        $response = $this->get(route('my.settings.organisations.index', ['whitelabel' => $whitelabel->id]));
        $response->assertSee($organisation6->title);
        $response->assertDontSee($organisation->title);

        // test json
        $response = $this->get(route('my.settings.organisations.index'), ['Accept' => 'application/json'])
            ->assertSuccessful();
        $response->assertJsonFragment([['id' => $organisation->id, 'title' => $organisation->title]]);
    }

    /**
     * @return void
     */
    public function testEdit(): void
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(static::resourceRoute() . '.edit');
        $this->activateAllOrg();

        $response = $this->assertSeeVisibleFormLabels(
            $resource,
            $route,
            static::editFormVisibleLabels(),
            static::editFormVisibleFields(),
        );
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            static::resourceRoute() . '.update',
            'put',
            false
        );
        $this->activateAllOrg();

        $this->updateAndTestData($route, $resource);
    }

    public function testDelete(): void
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            static::resourceRoute() . '.destroy',
            'delete',
            false
        );

        $this->activateAllOrg();

        $this->assertDatabaseHas(Organisation::class, ['id' => $resource->id, 'deleted_at' => null]);

        $orgUser = User::factory()->create();
        $orgUser->organisations()->attach($resource->id);
        $norma = Norma::factory()->create(['organisation_id' => $resource->id]);

        $this->delete(route('my.settings.organisations.destroy', $resource->id))->assertRedirect();

        $this->assertDatabaseMissing(Organisation::class, ['id' => $resource->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing(Norma::class, ['id' => $norma->id, 'deleted_at' => null]);
    }

    public function testCRMSyncOnUpdate(): void
    {
        Config::set('services.hubspot.enabled', true);

        $org = Organisation::factory()->create();

        $orgUser = User::factory()->create([
            'active' => true,
            'lifecycle_stage' => LifecycleStage::active(),
            'user_type' => UserType::customer()->value,
        ]);

        $role = Role::factory()->create(['title' => 'My Users']);

        $orgUser->organisations()->attach($org->id);
        $orgUser->roles()->attach($role->id);

        $this->mySuperUser($this->signIn());

        $update = Organisation::factory()->raw();

        $this->followingRedirects()
            ->put(route(static::resourceRoute() . '.update', $org->id), $update)
            ->assertSuccessful()
            ->assertSee('Successfully updated.');

        Bus::fake([CRMSyncOrganisationUsers::class]);

        $update = Organisation::factory()->raw();

        $this->followingRedirects()
            ->put(route(static::resourceRoute() . '.update', $org->id), $update)
            ->assertSuccessful()
            ->assertSee('Successfully updated.');

        Bus::assertDispatched(CRMSyncOrganisationUsers::class);
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(static::resourceRoute() . '.create');
        $this->activateAllOrg();

        $response = $this->assertSeeVisibleFormLabels(
            $resource,
            $route,
            static::createFormVisibleLabels(),
        );
    }

    /**
     * @return void
     */
    public function testStore(): void
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            static::resourceRoute() . '.store',
            'get',
            false
        );

        $this->activateAllOrg();

        $this->storeAndTestData($route, []);
    }

    /**
     * @return void
     */
    public function testModules(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.organisations.modules.index', ['organisation' => $org->id]);
        $this->assertForbiddenForNonAdmin($route, 'get');

        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();

        $response->assertSee('Tasks');
        $response->assertSee('Assess');
        $response->assertSee('Drives');
        $response->assertSee('Comments');
        $response->assertSee('Live Chat');
        $response->assertSee('Update Emails');
        $response->assertSee('Keyword Search');
        $response->assertSee('Search Requirements and Drives');
    }

    /**
     * @return void
     */
    public function testUpdatesModules(): void
    {
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $libry2 = Norma::factory()->for($org)->create();
        $route = route('my.settings.organisations.modules.update', ['organisation' => $org->id]);
        $this->assertForbiddenForNonAdmin($route, 'post');

        $this->activateAllOrg();

        $this->assertTrue($org->settings['modules']['tasks']);
        $this->assertFalse($org->settings['modules']['keyword_search']);
        $this->assertFalse($norma->settings['modules']['keyword_search']);
        $this->assertFalse($libry2->settings['modules']['keyword_search']);
        $this->followingRedirects()->post($route, ['tasks' => false, 'keyword_search' => true])->assertSuccessful();
        $org = $org->fresh();
        $norma = $norma->fresh();
        $libry2 = $libry2->fresh();
        $this->assertFalse($org->settings['modules']['tasks']);
        $this->assertTrue($org->settings['modules']['keyword_search']);
        $this->assertTrue($norma->settings['modules']['keyword_search']);
        $this->assertTrue($libry2->settings['modules']['keyword_search']);
    }

    public function testActivate(): void
    {
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.organisations.activate';
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['organisation' => $org]), 'post');

        $user->organisations()->attach($org, ['is_admin' => true]);
        $this->followingRedirects()->post(route($routeName, ['organisation' => $org]))->assertSuccessful();
    }
}
