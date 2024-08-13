<?php

namespace Tests\Feature\Auth;

use App\Enums\Collaborators\DocumentArchiveOptions;
use App\Enums\Collaborators\DocumentType;
use App\Enums\Collaborators\LanguageProficiency;
use App\Mail\Collaborators\CollaboratorAddressesUpdated;
use App\Mail\Collaborators\CollaboratorUnsubscribedFromCommunication;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\ProfileDocument;
use App\Models\Collaborators\ProfileLanguage;
use App\Models\Storage\Collaborate\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Macros\InlineDownload;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testGenerationOfCorrectProfile(): void
    {
        $this->validateAuthGuard(route('collaborate.profile.show'));

        $user = Collaborator::factory()->create();

        $this->signIn($user);

        $this->validateCollaborateRole(route('collaborate.profile.show'));

        $this->get(route('collaborate.profile.show'))
            ->assertSuccessful()
            ->assertSee('Profile')
            ->assertSee('Languages')
            ->assertSee('Documents')
            ->assertSee($user->fname);
    }

    /**
     * @return void
     */
    public function testSavingOfUserProfile(): void
    {
        $this->validateAuthGuard(route('collaborate.profile.update'), 'PUT');

        $user = Collaborator::factory()->create();

        $this->signIn($user);

        $this->validateCollaborateRole(route('collaborate.profile.show'), 'PUT');

        $this->withExceptionHandling()
            ->from(route('collaborate.profile.show'))
            ->put(route('collaborate.profile.update'), [])
            ->assertRedirect(route('collaborate.profile.show'))
            ->assertSessionHasErrors(['nationality', 'current_residency']);

        $this->withoutExceptionHandling();

        $payload = [
            '_tab' => 'languages',
            'nationality' => 'KE',
            'current_residency' => 'ZA',
            'address_line_1' => '00621 Nairobi',
            'address_line_2' => '00621 Nairobi',
            'address_line_3' => '00621 Nairobi',
            'address_country_code' => '254',
            'postal_address' => '00621',
            'hear_about' => 'Search engine',
            'languages' => ['eng'],
            'proficiency' => [LanguageProficiency::native()->value],
            'photo' => UploadedFile::fake()->image('test.png'),
        ];

        $this->assertDatabaseMissing(ProfileLanguage::class, ['profile_id' => $user->profile->id]);

        $this->followingRedirects()
            ->from(route('collaborate.profile.show', ['tab' => 'details']))
            ->put(route('collaborate.profile.update'), $payload)
            ->assertSee('Profile')
            ->assertSee('Languages')
            ->assertSee('Documents')
            ->assertSeeSelector('//*[@id="hear_about"]/option[@value="Search engine"][@selected]');

        $this->assertDatabaseHas(ProfileLanguage::class, [
            'profile_id' => $user->profile->id,
            'language_code' => 'eng',
            'proficiency' => LanguageProficiency::native()->value,
        ]);

        InlineDownload::apply();
        Storage::fake();

        $file = UploadedFile::fake()->image('test.png');

        $this->put(route('collaborate.profile.document.update', ['type' => DocumentType::IDENTIFICATION->field()]), [
            DocumentType::IDENTIFICATION->field() => $file,
            'reason_for_update' => DocumentArchiveOptions::EXPIRED->value,
        ])->assertSessionHasNoErrors();

        $document = ProfileDocument::where('type', DocumentType::IDENTIFICATION)
            ->where('profile_id', $user->profile->id)
            ->with(['attachment'])
            ->first();

        $attachment = $document->attachment;

        $this->assertNotNull($attachment);

        Storage::assertExists($attachment->path);

        $file = UploadedFile::fake()->image('tested.png');

        $this->put(route('collaborate.profile.document.update', ['type' => DocumentType::IDENTIFICATION->field()]), [
            DocumentType::IDENTIFICATION->field() => $file,
            'reason_for_update' => DocumentArchiveOptions::EXPIRED->value,
        ])->assertSessionHasNoErrors();

        $newDocument = ProfileDocument::with(['attachment'])
            ->where('type', DocumentType::IDENTIFICATION)
            ->where('profile_id', $user->profile->id)
            ->orderBy('id', 'desc')
            ->first();
        $newAttachment = $newDocument->attachment;

        $this->assertNotNull($newAttachment);

        Storage::assertExists($newAttachment->path);

        Storage::assertMissing($attachment->path);

        $this->assertDatabaseMissing(Attachment::class, ['id' => $attachment->id]);

        $this->withExceptionHandling()
            ->get(route('collaborate.profile.document.show', ['type' => DocumentType::IDENTIFICATION->field()]))
            ->assertNotFound();

        $newDocument->update(['approved_at' => now(), 'active' => true]);

        $this->withoutExceptionHandling()
            ->get(route('collaborate.profile.document.show', ['type' => DocumentType::IDENTIFICATION->field()]))
            ->assertSuccessful()
            ->assertInlineDownload('tested.png');

        $newDocument->delete();

        collect([
            DocumentType::IDENTIFICATION,
            DocumentType::TRANSCRIPT,
            DocumentType::VISA,
            DocumentType::VITAE,
        ])->each(function (DocumentType $type) use ($user) {
            $field = $type->field();
            $file = UploadedFile::fake()->image("{$field}.png");

            $this->put(route('collaborate.profile.document.update', ['type' => $field]), [
                $field => $file,
                'reason_for_update' => DocumentArchiveOptions::EXPIRED->value,
            ])
                ->assertSessionHasNoErrors();

            $typeDocument = ProfileDocument::with(['attachment'])
                ->where('type', $type)
                ->where('profile_id', $user->profile->id)
                ->orderBy('id', 'desc')
                ->first();

            $this->withExceptionHandling()
                ->get(route('collaborate.profile.document.show', ['type' => $field]))
                ->assertNotFound();

            $typeDocument->update(['approved_at' => now(), 'active' => true]);

            $this->withoutExceptionHandling()
                ->get(route('collaborate.profile.document.show', ['type' => $field]))
                ->assertSuccessful()
                ->assertInlineDownload("{$field}.png");
        });
    }

    /**
     * @return void
     */
    public function testSendsEmailCommunicationIsUnsubscribed(): void
    {
        Mail::fake();

        $user = Collaborator::factory()->create();

        $this->signIn($this->collaborateSuperUser($user));

        $user->load('profile');

        $profile = $user->profile;

        $this->assertTrue($profile->allows_communication);

        $payload = $profile->toArray();

        unset($payload['allows_communication']);
        $payload['linkedin_url'] = null;
        $payload['address_line_1'] = '00621 VM';
        $payload['address_line_2'] = '00621 VM';
        $payload['address_line_3'] = '00621 VM';
        $payload['address_country_code'] = '254';
        $payload['postal_address'] = '00621';

        Mail::assertNothingOutgoing();

        $this->put(route('collaborate.profile.update'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $profile->refresh();

        $this->assertFalse($profile->allows_communication);

        Mail::assertQueued(CollaboratorUnsubscribedFromCommunication::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $mail = new CollaboratorUnsubscribedFromCommunication($profile);

        $mail->assertSeeInHtml('Your email address will no longer receive marketing messages from us')
            ->hasBcc(config('collaborate.get_in_touch_email'));
    }

    /**
     * @return void
     */
    public function testAddressChangedMailIsDispatched(): void
    {
        Mail::fake();

        $user = Collaborator::factory()->create();

        $this->signIn($this->collaborateSuperUser($user));

        $user->load('profile');

        $profile = $user->profile;

        $this->assertNull($profile->address_line_1);
        $this->assertNull($profile->address_line_2);
        $this->assertNull($profile->address_line_3);
        $this->assertNull($profile->address_country_code);
        $this->assertNull($profile->postal_address);

        $payload = $profile->toArray();

        $payload['linkedin_url'] = null;
        $payload['address_line_1'] = '00621 VM';
        $payload['address_line_2'] = '00621 VM';
        $payload['address_line_3'] = '00621 VM';
        $payload['address_country_code'] = '254';
        $payload['postal_address'] = '00621';

        Mail::assertNothingOutgoing();

        $this->put(route('collaborate.profile.update'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $profile->refresh();

        $this->assertNotNull($profile->address_line_1);
        $this->assertNotNull($profile->address_line_2);
        $this->assertNotNull($profile->address_line_3);
        $this->assertNotNull($profile->address_country_code);
        $this->assertNotNull($profile->postal_address);

        Mail::assertQueued(CollaboratorAddressesUpdated::class, function ($mail) {
            return $mail->hasTo(
                config('collaborate.get_in_touch_email'));
        });

        $mail = new CollaboratorAddressesUpdated($profile);

        $mail->assertSeeInHtml('has updated their address.');
    }
}
