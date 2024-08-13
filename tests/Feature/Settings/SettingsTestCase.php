<?php

namespace Tests\Feature\Settings;

use App\Enums\Customer\OrganisationSwitcherMode;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;
use Tests\Feature\My\MyTestCase;
use Tests\Feature\Traits\TestsVisibleFormLabels;

class SettingsTestCase extends MyTestCase
{
    use TestsVisibleFormLabels;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resource(): string
    {
        throw new Exception('You need to implement the resource() method');
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
        throw new Exception('You need to implement the resourceRoute() method');
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
        throw new Exception('You need to implement the editFormVisibleLabels() method');
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
        throw new Exception('You need to implement the editFormVisibleFields() method');
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
        throw new Exception('You need to implement the createFormVisibleLabels() method');
    }

    /**
     * Shared helper for checking the user can't access if not admin.
     *
     * @param string $route
     * @param string $method
     *
     * @return User
     */
    protected function assertForbiddenForNonAdmin(string $route, string $method, ?User $user = null): User
    {
        $user ??= $this->signIn();
        $role = Role::factory()->my()->create();
        $user->roles()->attach($role);

        // user without any organisation admin access should not be allowed
        $response = $this->withExceptionHandling()->{$method}($route);
        $response->assertForbidden();

        return $user;
    }

    /**
     * Once logged in assert that the user can access the given route when the org the user does have access to is activated.
     *
     * @param string $route
     * @param string $method
     *
     * @return void
     */
    protected function assertCanAccessAfterOrgActivate(string $route, string $method): TestResponse
    {
        $response = $this->withExceptionHandling()->{$method}($route);
        $response->assertRedirect();
        $response = $this->followRedirects($response);
        $response->assertSuccessful();

        return $response;
    }

    /**
     * @param User|null $user
     *
     * @return void
     */
    protected function activateAllOrg(?User $user = null): User
    {
        $user = $this->mySuperUser($user);
        $user->flushComputedPermissions();
        $this->signIn($user);
        $response = $this->post(route('my.settings.organisations.activate.all'));
        $response->assertRedirect();

        return $user;
    }

    /**
     * @param Organisation $organisation
     *
     * @return void
     */
    protected function withActivatedOrg(Organisation $organisation): static
    {
        $data = [
            config('session-keys.customer.active-organisation') => $organisation->id,
            config('session-keys.customer.organisation-mode') => OrganisationSwitcherMode::single()->value,
        ];

        /** @var static */
        return $this->withSession($data);
    }

    /**
     * @param Organisation $organisation
     *
     * @return void
     */
    protected function withAllOrgMode(): static
    {
        $data = [
            config('session-keys.customer.active-organisation') => 'all',
            config('session-keys.customer.organisation-mode') => OrganisationSwitcherMode::all()->value,
        ];

        return $this->withSession($data);
    }

    /**
     * @return array
     */
    protected function createResourceAndAssertForbidden(string $routeName, string $method = 'get', bool $forOrg = false, bool $allOrgMode = false): array
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $resourceClass = static::resource();
        $factory = $resourceClass::factory();
        if ($forOrg) {
            $factory = $factory->for($org);
        }
        $resource = $factory->create();

        $route = route($routeName, $resource->id);
        $user = $this->assertForbiddenForNonAdmin($route, $method, $user);

        if ($allOrgMode) {
            $this->signIn($this->mySuperUser());
            $this->withAllOrgMode();
        }

        $user->organisations()->attach($org, ['is_admin' => true]);

        return [$user, $resource, $route, $org];
    }

    /**
     * @return void
     */
    protected function runEditTest(bool $forOrg = false, bool $allOrgMode = false): array
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            routeName: static::resourceRoute() . '.edit',
            forOrg: $forOrg,
            allOrgMode: $allOrgMode,
        );

        $response = $this->assertSeeVisibleFormLabels(
            $resource,
            $route,
            static::editFormVisibleLabels(),
            static::editFormVisibleFields(),
        );

        // $response = $this->get($route)->assertForbidden();
        return [$user, $org, $resource, $route, $response];
    }

    protected function runUpdateTest(bool $forOrg = false, bool $allOrgMode = false): array
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            static::resourceRoute() . '.update',
            'put',
            $forOrg,
            $allOrgMode
        );

        // $response = $this->get($route)->assertForbidden();
        $response = $this->updateAndTestData($route, $resource);

        $editRoute = route(static::resourceRoute() . '.edit', $resource->id);

        return [$user, $org, $resource, $route, $response, $editRoute];
    }

    protected function updateAndTestData(string $route, Model $resource): TestResponse
    {
        $resourceClass = static::resource();
        $updateData = $resourceClass::factory()->raw();

        $this->followingRedirects()->put($route, $updateData)->assertSuccessful();

        $editRoute = route(static::resourceRoute() . '.edit', $resource->id);
        $this->get($editRoute)->assertSuccessful();

        $response = $this->assertSeeVisibleFormLabels(
            $updateData,
            $editRoute,
            static::editFormVisibleLabels(),
            static::editFormVisibleFields(),
        );

        return $response;
    }

    /**
     * @return void
     */
    protected function runCreateTest(bool $allOrgMode = false): array
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            routeName: static::resourceRoute() . '.create',
            allOrgMode: $allOrgMode,
        );

        $response = $this->assertSeeVisibleFormLabels(
            $resource,
            $route,
            static::createFormVisibleLabels(),
        );

        // $response = $this->get($route)->assertForbidden();
        return [$user, $org, $resource, $route, $response];
    }

    /**
     * @param array $skipFields
     * @param bool  $forOrg
     * @param bool  $allOrgMode
     *
     * @return array
     */
    protected function runStoreTest(array $skipFields, bool $forOrg = false, bool $allOrgMode = false): array
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            static::resourceRoute() . '.store',
            'get',
            $forOrg,
            $allOrgMode,
        );

        // $response = $this->get($route)->assertForbidden();

        $response = $this->storeAndTestData($route, $skipFields);

        return [$user, $org, $resource, $route, $response];
    }

    /**
     * @param string $route
     * @param array  $skipFields
     *
     * @return TestResponse
     */
    protected function storeAndTestData(string $route, array $skipFields): TestResponse
    {
        $resourceClass = static::resource();
        $createData = $resourceClass::factory()->raw();

        $count = $resourceClass::count();
        $response = $this->followingRedirects()->post($route, $createData)->assertSuccessful();
        $this->assertGreaterThan($count, $resourceClass::count());

        $lastCreated = $resourceClass::all()->last();
        foreach ($createData as $key => $value) {
            if (in_array($key, $skipFields)) {
                continue;
            }

            $this->assertEquals($value, $lastCreated[$key]);
        }

        return $response;
    }

    /**
     * @return array
     */
    protected function runDestroyTest(bool $forOrg = false, bool $allOrgMode = false): array
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            static::resourceRoute() . '.destroy',
            'delete',
            $forOrg,
            $allOrgMode,
        );

        // $response = $this->get($route)->assertForbidden();

        $response = $this->destroyAndTestData($route);

        return [$user, $org, $resource, $route, $response];
    }

    /**
     * @param string $route
     *
     * @return void
     */
    protected function destroyAndTestData(string $route): void
    {
        $resourceClass = static::resource();
        $count = $resourceClass::count();
        $response = $this->followingRedirects()->delete($route)->assertSuccessful();
        $this->assertLessThan($count, $resourceClass::count());
    }
}
