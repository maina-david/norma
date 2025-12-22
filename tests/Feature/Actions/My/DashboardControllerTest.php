<?php

namespace Tests\Feature\Actions\My;

use App\Enums\System\NormaModule;
use Tests\Feature\My\MyTestCase;

class DashboardControllerTest extends MyTestCase
{
    public function testDashboardRenders(): void
    {
        /** @var \App\Models\Customer\Norma $norma */
        /** @var \App\Models\Customer\Organisation $org */
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $norma->enableModule(NormaModule::actions());

        $this->get(route('my.actions.tasks.dashboard'))
            ->assertSuccessful()
            ->assertSee('my-norma');

        $this->activateAllStreams($user);

        $this->get(route('my.actions.tasks.dashboard'))
            ->assertSuccessful()
            ->assertSee('my-norma');
    }
}
