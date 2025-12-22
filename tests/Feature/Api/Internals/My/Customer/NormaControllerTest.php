<?php

namespace Tests\Feature\Api\Internals\My\Customer;

use App\Models\Customer\Norma;
use Tests\Feature\My\MyTestCase;

class NormaControllerTest extends MyTestCase
{
    public function testListingAvailableNormas(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $otherNorma = Norma::factory()->create(['organisation_id' => $org->id]);
        $notForUserNorma = Norma::factory()->create(['organisation_id' => $org->id]);

        $otherNorma->users()->attach($user->id);

        $this->getJson(route('api.my.organisation.normas.index'))
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $norma->id],
                    ['id' => $otherNorma->id],
                ],
            ]);
    }
}
