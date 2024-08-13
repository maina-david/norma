<?php

namespace Tests\Feature\Collaborators\Collaborate\Applications;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Mail\Collaborators\ProvisionalAcceptanceMail;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationMailControllerTest extends TestCase
{
    public function testResendingMail(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());

        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()
            ->has(Profile::factory()->state(['user_id' => null]), 'profile')
            ->create(['stage' => ApplicationStage::one()]);

        $this->post(route('collaborate.collaborator-applications.approve', ['application' => $application->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(CollaboratorApplication::class, [
            'id' => $application->id,
            'application_state' => ApplicationStatus::approved()->value,
        ]);

        Mail::fake();

        Mail::assertNothingOutgoing();

        $this->post(route('collaborate.collaborator-applications.resend', ['application' => $application->id]))
            ->assertRedirect();

        Mail::assertQueued(ProvisionalAcceptanceMail::class);
    }
}
