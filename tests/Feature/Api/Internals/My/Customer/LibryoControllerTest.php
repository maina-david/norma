<?php

namespace Tests\Feature\Api\Internals\My\Customer;

use App\Models\Customer\Libryo;
use Tests\Feature\My\MyTestCase;

class LibryoControllerTest extends MyTestCase
{
    public function testListingAvailableLibryos(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $otherLibryo = Libryo::factory()->create(['organisation_id' => $org->id]);
        $notForUserLibryo = Libryo::factory()->create(['organisation_id' => $org->id]);

        $otherLibryo->users()->attach($user->id);

        $this->getJson(route('api.my.organisation.libryos.index'))
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $libryo->id],
                    ['id' => $otherLibryo->id],
                ],
            ]);
    }
}
