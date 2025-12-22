<?php

namespace Tests\Feature\Notify\My;

use App\Models\Notify\LegalUpdate;
use Tests\Feature\My\MyTestCase;

class LegalUpdateUserStreamControllerTest extends MyTestCase
{
    public function testUserStatuses(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $update = LegalUpdate::factory()->create();
        $routeName = 'my.notify.legal-updates.users.index';
        $response = $this->get(route($routeName, ['update' => $update]));

        $response->assertDontSee($user->fullName);

        $user->legalupdates()->attach($update);
        $response = $this->get(route($routeName, ['update' => $update]));
        $response->assertSee($user->fullName);
        $response->assertSee('bg-negative');

        $user->legalupdates()->updateExistingPivot($update, ['read_status' => true]);
        $response = $this->get(route($routeName, ['update' => $update]));
        $response->assertSee($user->fullName);
        $response->assertDontSee('bg-negative');
        $response->assertSee('bg-warning');

        $user->legalupdates()->updateExistingPivot($update, ['understood_status' => true]);
        $response = $this->get(route($routeName, ['update' => $update]));
        $response->assertDontSee('bg-warning');
        $response->assertSee('bg-positive');

        $response = $this->get(route($routeName, ['update' => $update, 'search' => $user->fname]));
        $response->assertSee($user->fullName);
    }
}
