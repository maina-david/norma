<?php

namespace Tests\Feature\Collaborators\Collaborate\Applications;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class StageTwoControllerTest extends TestCase
{
    public function testUpdatingTheApplication(): void
    {
        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()
            ->has(Profile::factory()->state(['user_id' => null]), 'profile')
            ->create(['stage' => ApplicationStage::one()]);

        $link = URL::temporarySignedRoute('collaborate.collaborator-application.stage-two.create', now()->addDays(14), ['application' => $application->id]);

        $this->withExceptionHandling()->get($link)->assertNotFound();

        $application->update(['stage' => ApplicationStage::one()->value, 'application_state' => ApplicationStatus::approved()->value]);

        $this->withoutExceptionHandling()
            ->get($link)
            ->assertSee('Postal Address');

        $payload = [
            'address_line_1' => $this->faker->address(),
            'postal_address' => $this->faker->address(),
            'identity' => UploadedFile::fake()->image('identity.jpg'),
            'visa' => UploadedFile::fake()->image('visa.jpg'),
            'process_personal_data' => true,
            'permission_to_work' => true,
        ];

        $response = $this->put(route('collaborate.collaborator-application.stage-two.update', ['application' => $application->id]), $payload)
            ->assertRedirect(route('collaborate.collaborator-application.stage-two.thanks', ['application' => $application->id]));

        $this->followRedirects($response)
            ->assertSee('We will review your application');
    }
}
