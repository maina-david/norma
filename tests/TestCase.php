<?php

namespace Tests;

use App\Enums\Application\ApplicationType;
use App\Enums\Workflows\CollaborateSessionKey;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use App\Services\Search\LibryoAI\LibryoAISearchEngine;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Testing\Assert;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeilisearchEngine;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Mockery\MockInterface;
use ReflectionException;
use Symfony\Component\DomCrawler\Crawler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling([
            AuthenticationException::class,
            ValidationException::class,
        ]);
        $this->registerMacros();
        $this->createTmpDir();
        $this->withoutVite();
        $this->registerScout();
        $this->freezeTime();
    }

    /**
     * @return void
     */
    private function registerMacros(): void
    {
        TestResponse::macro('assertSeeSelector', function (string $filter, ?string $message = null) {
            $crawler = new Crawler($this->getContent());

            $filtered = Str::startsWith($filter, '/') ? $crawler->filterXPath($filter) : $crawler->filter($filter);
            Assert::assertTrue(count($filtered) > 0, $message ?? 'Failed to find the selector in the html response.');

            return $this;
        });
        TestResponse::macro('assertDontSeeSelector', function (string $filter, ?string $message = null) {
            $crawler = new Crawler($this->getContent());

            $filtered = Str::startsWith($filter, '/') ? $crawler->filterXPath($filter) : $crawler->filter($filter);
            Assert::assertFalse(count($filtered) > 0, $message ?? 'Failed to assert that the selector is missing in the html response.');

            return $this;
        });
    }

    /**
     * Creates tmp directory if it doesn't exist.
     *
     * @return void
     */
    public function createTmpDir(): void
    {
        if (!is_dir(storage_path('app/tmp'))) {
            mkdir(storage_path('app/tmp'));
        }
    }

    /**
     * Create a superuser role for the given application type.
     *
     * @param ApplicationType $type
     *
     * @return Role
     */
    private function superRole(ApplicationType $type): Role
    {
        return Role::create([
            'title' => "{$type->value} Super Users",
            'app' => $type->value,
            'permissions' => [config('permissions.superuser-name')],
        ]);
    }

    private function normalUserRole(ApplicationType $type, array $permissions = []): Role
    {
        return Role::create([
            'title' => "{$type->value} Users",
            'app' => $type->value,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Create a collaborate superuser role.
     *
     * @return Role
     */
    protected function collaborateSuper(): Role
    {
        return $this->superRole(ApplicationType::collaborate());
    }

    /**
     * Create an admin superuser role.
     *
     * @return Role
     */
    protected function adminSuper(): Role
    {
        return $this->superRole(ApplicationType::admin());
    }

    /**
     * Create a my superuser role.
     *
     * @return Role
     */
    protected function mySuper(): Role
    {
        return $this->superRole(ApplicationType::my());
    }

    /**
     * Create a collaborate normal user role.
     *
     * @param array<string> $permissions
     *
     * @return Role
     */
    protected function collaborateNormal(array $permissions = []): Role
    {
        return $this->normalUserRole(ApplicationType::collaborate(), $permissions);
    }

    /**
     * Create a my normal user role.
     *
     * @param array<string> $permissions
     *
     * @return Role
     */
    protected function myNormal(array $permissions = []): Role
    {
        return $this->normalUserRole(ApplicationType::my(), $permissions);
    }

    /**
     * Validate that the given endpoint cannot be accessed unless the user is logged in.
     *
     * @param string $route
     * @param string $method
     *
     * @return void
     */
    protected function validateAuthGuard(string $route, string $method = 'get'): void
    {
        $this->assertGuest();

        $this->{$method}($route)->assertRedirect(route('login'));
    }

    /**
     * Validate that the given endpoint cannot be accessed unless the user has
     * the correct role.
     *
     * @param string $route
     * @param string $method
     *
     * @return void
     */
    private function validateRole(ApplicationType $type, string $route, string $method = 'get'): void
    {
        $method = strtolower($method);

        $this->assertAuthenticated();

        $this->withExceptionHandling()
            ->{$method}($route)
            ->assertForbidden();

        $role = $type->value . 'Super';
        $role = $this->{$role}();

        $user = $this->app['auth']->guard()->user();
        $user->roles()->attach($role->id);
        $user->flushComputedPermissions();

        $this->followingRedirects()
            ->{$method}($route)
            ->assertSuccessful();

        $this->withoutExceptionHandling();
    }

    /**
     * Validate that the given admin endpoint cannot be accessed unless the user has
     * the correct role.
     *
     * @param string $route
     * @param string $method
     *
     * @return void
     */
    protected function validateAdminRole(string $route, string $method = 'get'): void
    {
        $this->validateRole(ApplicationType::admin(), $route, $method);
    }

    /**
     * Validate that the given collaborate endpoint cannot be accessed unless the user has
     * the correct role.
     *
     * @param string $route
     * @param string $method
     *
     * @return void
     */
    protected function validateCollaborateRole(string $route, string $method = 'get'): void
    {
        $this->validateRole(ApplicationType::collaborate(), $route, $method);
    }

    /**
     * Validate that the given my endpoint cannot be accessed unless the user has
     * the correct role.
     *
     * @param string $route
     * @param string $method
     *
     * @return void
     */
    protected function validateMyRole(string $route, string $method = 'get'): void
    {
        $this->validateRole(ApplicationType::my(), $route, $method);
    }

    /**
     * Sign in with the current user.
     *
     * @param User|null $user
     *
     * @return User
     */
    protected function signIn(?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    /**
     * Sign in with the current user.
     *
     * @param Collaborator|null $user
     *
     * @return User
     */
    protected function collaboratorSignIn(?Collaborator $user = null): User
    {
        return $this->signIn($user ?? Collaborator::factory()->create());
    }

    /**
     * Get a super user for my.libryo.
     *
     * @param User|null|null $user
     *
     * @return User
     */
    protected function mySuperUser(?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        $user->roles()->attach($this->mySuper());
        $user->flushComputedPermissions();

        return $user;
    }

    /**
     * Get a super user for admin.
     *
     * @param User|null|null $user
     *
     * @return User
     */
    protected function adminSuperUser(?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        $user->roles()->attach($this->adminSuper());

        return $user;
    }

    /**
     * Get a super user for collaborate.
     *
     * @param User|null|null $user
     *
     * @return User
     */
    protected function collaborateSuperUser(?User $user = null): User
    {
        $user = $user ?? Collaborator::factory()->create();
        $user->roles()->attach($this->collaborateSuper());

        return $user;
    }

    /**
     * Get a super user for my.libryo.
     *
     * @param array<string>  $permissions
     * @param User|null|null $user
     *
     * @return User
     */
    protected function myNormalUser(array $permissions = [], ?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        $user->roles()->attach($this->myNormal($permissions));
        $user->flushComputedPermissions();

        return $user;
    }

    /**
     * Get a super user for collaborate.
     *
     * @param array<string>          $permissions
     * @param Collaborator|null|null $user
     *
     * @return Collaborator
     */
    protected function collaborateNormalUser(array $permissions = [], ?Collaborator $user = null): Collaborator
    {
        $user = $user ?? Collaborator::factory()->create();
        // $profile = Profile::factory()->for($user)->create();
        $user->roles()->attach($this->collaborateNormal($permissions));
        $user->flushComputedPermissions();

        return $user;
    }

    /**
     * Apply the task types permissions.
     *
     * @throws ReflectionException
     *
     * @return $this
     */
    protected function applyTaskTypesPermissions(): self
    {
        TaskType::reCacheAll();

        $check = function (User $user, string $permission) {
            $app = explode('.', $permission)[0];

            return in_array("{$app}." . config('permissions.superuser-name'), $user->permissions)
                || in_array($permission, $user->permissions);
        };

        Role::collaboratePermissions(true)->each(function ($permission) use ($check) {
            Gate::define($permission, fn (User $user) => $check($user, $permission));
        });

        return $this;
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param string $uri
     * @param array  $headers
     *
     * @return TestResponse
     */
    protected function turboGet($uri, array $headers = [])
    {
        $headers['turbo-frame'] = $headers['turbo-frame'] ?? 'for-turbo-frame';

        return $this->get($uri, $headers);
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return TestResponse
     */
    public function turboPost($uri, array $data = [], array $headers = [])
    {
        $headers['turbo-frame'] = $headers['turbo-frame'] ?? 'for-turbo-frame';

        return $this->post($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return TestResponse
     */
    public function turboPut($uri, array $data = [], array $headers = [])
    {
        $headers['turbo-frame'] = $headers['turbo-frame'] ?? 'for-turbo-frame';

        return $this->put($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request.
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return TestResponse
     */
    public function turboDelete($uri, array $data = [], array $headers = [])
    {
        $headers['turbo-frame'] = $headers['turbo-frame'] ?? 'for-turbo-frame';

        return $this->delete($uri, $data, $headers);
    }

    /**
     * Set the task and expression to the session.
     *
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return TestCase
     */
    public function setExpression(WorkExpression $expression, ?Task $task = null)
    {
        return $this->withSession([
            CollaborateSessionKey::CURRENT_EXPRESSION->value => $expression,
            CollaborateSessionKey::CURRENT_TASK->value => $task,
        ]);
    }

    protected function registerScout()
    {
        $index = $this->mock(Indexes::class, function (MockInterface $mock) {
            $mock->shouldReceive('search')->andReturn(['hits' => [], 'nbHits' => 0, 'totalHits' => 0]);
            $mock->shouldReceive('rawSearch')->andReturn(['hits' => [], 'nbHits' => 0, 'totalHits' => 0]);
            $mock->shouldReceive('deleteDocuments')->andReturn([]);
            $mock->shouldReceive('addDocuments')->andReturn([]);
        });

        $search = $this->mock(Client::class, function (MockInterface $mock) use ($index) {
            $mock->shouldReceive('index')->andReturn($index);
        });

        $mock = $this->partialMock(EngineManager::class, function (MockInterface $mock) use ($search) {
            $mock->shouldReceive('createMeilisearchDriver')->andReturn(new MeilisearchEngine($search, false));
        });

        $mock->extend('libryo-ai', function () {
            return new LibryoAISearchEngine(app(\App\Http\Services\LibryoAI\Client::class));
        });
    }

    /**
     * Put the given content.
     *
     * @param string $url
     * @param string $content
     *
     * @return \Illuminate\Testing\TestResponse
     */
    protected function putContent(string $url, string $content): TestResponse
    {
        // pending job exists so discard
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();

        return $this->call('PUT', $url, [], $cookies, [], $server, $content);
    }
}
