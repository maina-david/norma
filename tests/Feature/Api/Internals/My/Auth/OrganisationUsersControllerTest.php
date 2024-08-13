<?php

namespace Tests\Feature\Api\Internals\My\Auth;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Tests\Feature\My\MyTestCase;

class OrganisationUsersControllerTest extends MyTestCase
{
    public function testFetchingOrganisationUsers(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $otherLibryo = Libryo::factory()->create(['organisation_id' => $org->id]);

        $nonLibryo = User::factory()->create();
        $nonLibryo->libryos()->attach($otherLibryo->id);
        $nonLibryo->organisations()->attach($org->id);

        $loggedIn = $this->signIn($this->mySuperUser());

        $this->getJson(route('api.my.organisation.users.index', ['libryo' => true]))
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
                    ['id' => $nonLibryo->id, 'name' => $nonLibryo->full_name],
                ],
            ]);

        $this->signIn($user);

        $users = User::whereKey([$user->id, $nonLibryo->id])
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
