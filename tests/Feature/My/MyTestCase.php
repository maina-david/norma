<?php

namespace Tests\Feature\My;

use App\Enums\System\NormaModule;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
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
        app(ActiveNormasManager::class)->activateAll($user);

        return $user;
    }

    public function initUserNormaOrg(): array
    {
        $user = $this->signIn($this->mySuperUser());
        $org = Organisation::factory()->create();
        $org->enableModule(NormaModule::actions());
        $org->enableModule(NormaModule::comply());
        $norma = Norma::factory()->for($org)->create();
        $user->normas()->attach($norma);
        $user->organisations()->attach($org);

        app(ActiveNormasManager::class)->activate($user, $norma);

        return [$user, $norma, $org];
    }

    public function validateActionsIsDisabled(string $route, Norma $norma, Organisation $organisation, string $method = 'get', array $params = [])
    {
        $this->followingRedirects()
            ->{$method}($route, $params)
            ->assertSuccessful()
            ->assertSee('Actions has not been enabled.');

        $norma->enableModule(NormaModule::actions());

        $organisation->refresh();
        $norma->refresh();
    }
}
