<?php

namespace Tests\Feature\Actions\My;

use App\Enums\System\LibryoModule;
use Tests\Feature\My\MyTestCase;

class DashboardControllerTest extends MyTestCase
{
    public function testDashboardRenders(): void
    {
        /** @var \App\Models\Customer\Libryo $libryo */
        /** @var \App\Models\Customer\Organisation $org */
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $libryo->enableModule(LibryoModule::actions());

        $this->get(route('my.actions.tasks.dashboard'))
            ->assertSuccessful()
            ->assertSee('my-libryo');

        $this->activateAllStreams($user);

        $this->get(route('my.actions.tasks.dashboard'))
            ->assertSuccessful()
            ->assertSee('my-libryo');
    }
}
