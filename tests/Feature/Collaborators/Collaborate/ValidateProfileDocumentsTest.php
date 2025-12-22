<?php

namespace Tests\Feature\Collaborators\Collaborate;

use App\Enums\Collaborators\DocumentType;
use App\Jobs\Collaborators\ValidateProfileDocuments;
use App\Mail\Collaborators\ProfileDocumentValidity;
use App\Models\Collaborators\Collaborator;
use App\Models\Storage\Collaborate\Attachment;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ValidateProfileDocumentsTest extends TestCase
{
    /**
     * @return void
     */
    public function testItIdentifiesInvalidDocuments(): void
    {
        $user = Collaborator::factory()->create();
        $attachment = Attachment::factory()->create();

        Task::factory()->create([
            'assigned_to_id' => $user->id,
            'created_at' => now()->subMonths(5),
        ]);

        $document = $user->profile->documents()->create([
            'type' => DocumentType::IDENTIFICATION,
            'attachment_id' => $attachment->id,
            'active' => true,
            'approved_at' => now(),
        ]);

        Mail::fake();

        Mail::assertNothingQueued();

        ValidateProfileDocuments::dispatch();

        Mail::assertNothingQueued();

        $document->update(['valid_to' => now()]);

        ValidateProfileDocuments::dispatch();

        Mail::assertQueued(fn (ProfileDocumentValidity $mail) => $mail->document->id === $document->id);
    }

    /**
     * @return void
     */
    public function testItDoesNotSendEmailToInactiveUserCollaborator(): void
    {
        $user = Collaborator::factory()->create(['active' => false]);
        $attachment = Attachment::factory()->create();

        $document = $user->profile->documents()->create([
            'type' => DocumentType::IDENTIFICATION,
            'attachment_id' => $attachment->id,
            'active' => true,
            'approved_at' => now(),
            'valid_to' => now()->addDays(7),
        ]);

        Mail::fake();
        Mail::assertNothingQueued();

        ValidateProfileDocuments::dispatch();

        Mail::assertNothingQueued();

        Mail::assertNotQueued(ProfileDocumentValidity::class);
    }

    /**
     * @return void
     */
    public function testItDoesNotSendEmailToCollaboratorAccessedSixMonthsAgo(): void
    {
        $user = Collaborator::factory()->create(['updated_at' => now()->subMonths(7)]);
        $attachment = Attachment::factory()->create();

        $document = $user->profile->documents()->create([
            'type' => DocumentType::IDENTIFICATION,
            'attachment_id' => $attachment->id,
            'active' => true,
            'approved_at' => now(),
            'valid_to' => now()->addDays(7),
        ]);

        Mail::fake();
        Mail::assertNothingQueued();

        ValidateProfileDocuments::dispatch();

        Mail::assertNothingQueued();

        Mail::assertNotQueued(ProfileDocumentValidity::class);
    }

    /**
     * @return void
     */
    public function testItSendsCorrectContent(): void
    {
        $user = Collaborator::factory()->create();
        $attachment = Attachment::factory()->create();

        $expired = $user->profile->documents()->create([
            'type' => DocumentType::IDENTIFICATION,
            'attachment_id' => $attachment->id,
            'active' => true,
            'approved_at' => now(),
            'valid_to' => now(),
        ]);

        $expiring = $user->profile->documents()->create([
            'type' => DocumentType::IDENTIFICATION,
            'attachment_id' => $attachment->id,
            'active' => true,
            'approved_at' => now(),
            'valid_to' => now()->addDays(7),
        ]);

        $mail = new ProfileDocumentValidity($expired);
        $mail->assertSeeInHtml('This is to notify you that your uploaded Identification Document on Norma Collaborate has expired');

        $mail = new ProfileDocumentValidity($expiring);
        $mail->assertSeeInHtml('This is to notify you that your uploaded Identification Document on Norma Collaborate will expire in 7 days! Please update your profile with a new one to avoid account deactivation.');
    }
}
