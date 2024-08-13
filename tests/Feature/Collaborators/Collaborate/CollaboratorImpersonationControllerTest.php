<?php

namespace Tests\Feature\Collaborators\Collaborate;

use App\Models\Auth\Role;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Profile;
use Tests\TestCase;

class CollaboratorImpersonationControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testAllowsImpersonation(): void
    {
        $user = $this->collaborateSuperUser();

        $this->signIn($user);

        $toImpersonate = Collaborator::factory()
            ->has(Profile::factory())
            ->has(Role::factory()->collaborate())
            ->create(['active' => false]);

        $this->get(route('collaborate.collaborators.show', $toImpersonate))
            ->assertSuccessful()
            ->assertDontSee('Impersonate');

        $toImpersonate->update(['active' => true]);

        $this->get(route('collaborate.collaborators.show', $toImpersonate))
            ->assertSuccessful()
            ->assertSee('Impersonate');

        $this->post(route('collaborate.collaborator.impersonate', $toImpersonate))
            ->assertRedirect(route('collaborate.dashboard'))
            ->assertSessionHas('impersonating');

        $this->get(route('collaborate.collaborator.impersonate.leave'))
            ->assertRedirect(route('collaborate.dashboard'))
            ->assertSessionMissing('impersonating');
    }
}
