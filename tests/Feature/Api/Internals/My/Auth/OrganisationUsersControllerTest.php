<?php

namespace Tests\Feature\Api\Internals\My\Auth;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use Tests\Feature\My\MyTestCase;

class OrganisationUsersControllerTest extends MyTestCase
{
    public function testFetchingOrganisationUsers(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $otherNorma = Norma::factory()->create(['organisation_id' => $org->id]);

        $nonNorma = User::factory()->create();
        $nonNorma->normas()->attach($otherNorma->id);
        $nonNorma->organisations()->attach($org->id);

        $loggedIn = $this->signIn($this->mySuperUser());

        $this->getJson(route('api.my.organisation.users.index', ['norma' => true]))
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $loggedIn->id, 'name' => $loggedIn->full_name],
                    ['id' => $user->id, 'name' => $user->full_name],
                ],
            ])
            ->assertJsonMissing([
                'data' => [
                    ['id' => $nonNorma->id, 'name' => $nonNorma->full_name],
                ],
            ]);

        $this->signIn($user);

        $users = User::whereKey([$user->id, $nonNorma->id])
            ->orderBy('fname')
            ->get()
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->full_name])
            ->all();

        $this->getJson(route('api.my.organisation.users.index'))
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJson(['data' => $users]);
    }
}
