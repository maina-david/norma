<?php

namespace Tests\Feature\My;

use App\Enums\System\LibryoModule;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Tests\TestCase;
use Tests\Traits\CompilesStream;
use Throwable;

class MyTestCase extends TestCase
{
    use CompilesStream;

    /**
     * @param User|null $user
     *
     * @return void
     */
    protected function activateAllStreams(?User $user = null, ?Organisation $organisation = null): User
    {
        $user = $this->mySuperUser($user);
        $user->flushComputedPermissions();
        $org = $organisation ?? Organisation::factory()->create();

        try {
            $user->organisations()->attach($org);
        } catch (Throwable) {
        }

        $this->signIn($user);
        app(ActiveLibryosManager::class)->activateAll($user);

        return $user;
    }

    public function initUserLibryoOrg(): array
    {
        $user = $this->signIn($this->mySuperUser());
        $org = Organisation::factory()->create();
        $org->enableModule(LibryoModule::actions());
        $org->enableModule(LibryoModule::comply());
        $libryo = Libryo::factory()->for($org)->create();
        $user->libryos()->attach($libryo);
        $user->organisations()->attach($org);

        app(ActiveLibryosManager::class)->activate($user, $libryo);

        return [$user, $libryo, $org];
    }

    public function validateActionsIsDisabled(string $route, Libryo $libryo, Organisation $organisation, string $method = 'get', array $params = [])
    {
        $this->followingRedirects()
            ->{$method}($route, $params)
            ->assertSuccessful()
            ->assertSee('Actions has not been enabled.');

        $libryo->enableModule(LibryoModule::actions());

        $organisation->refresh();
        $libryo->refresh();
    }
}
