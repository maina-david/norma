<?php

namespace Tests\Feature\Corpus\Collaborate;

use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    public function testLoadingTheDashboard(): void
    {
        $user = $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->get(route('collaborate.dashboard'))
            ->assertSuccessful()
            ->assertSee($user->full_name);

        $this->get(route('collaborate.dashboard'), ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertDontSee($user->full_name);
    }
}
