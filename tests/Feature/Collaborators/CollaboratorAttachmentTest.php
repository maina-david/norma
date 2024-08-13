<?php

namespace Tests\Feature\Collaborators;

use App\Enums\Collaborators\ContractType;
use App\Enums\Collaborators\DocumentType;
use App\Enums\Collaborators\IdentityDocumentType;
use App\Enums\Storage\FileType;
use App\Models\Collaborators\Collaborator;
use App\Models\Storage\Collaborate\Attachment;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Macros\InlineDownload;
use Tests\TestCase;

class CollaboratorAttachmentTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItSavesTheContract(): void
    {
        Storage::fake();

        $type = FileType::collaborators();
        $file = UploadedFile::fake()->image('passport.jpg');
        $attachment = new Attachment();
        $attachment->saveFile($file);

        Storage::assertExists(config("filesystems.attachments.{$type}.path"));
        Storage::assertExists($attachment->path);

        $this->signIn($this->collaborateSuperUser());

        /** @var Collaborator $collaborator */
        $collaborator = Collaborator::factory()->create();

        $payload = [
            'contract' => UploadedFile::fake()->image('contract.jpg'),
            'contract_signed' => true,
            'contract_type' => ContractType::zeroHour()->value,
        ];

        $this->followingRedirects()
            ->put(route('collaborate.profile.document.upload', ['type' => DocumentType::CONTRACT->field(), 'profile' => $collaborator->profile->id]), $payload)
            ->assertSuccessful()
            ->assertSessionHasNoErrors();
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItPreviewsAndDownloadsAndValidatesTheDocuments(): void
    {
        $this->assertGuest();

        Storage::fake();

        /** @var Collaborator $collaborator */
        $collaborator = Collaborator::factory()->create();

        $contract = $collaborator->profile->documents()->create([
            'type' => DocumentType::CONTRACT,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::CONTRACT->field() . '.jpg'))->id,
        ]);
        $passport = $collaborator->profile->documents()->create([
            'type' => DocumentType::IDENTIFICATION,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::IDENTIFICATION->field() . '.jpg'))->id,
        ]);
        $schedule = $collaborator->profile->documents()->create([
            'type' => DocumentType::SCHEDULE_A,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::SCHEDULE_A->field() . '.jpg'))->id,
        ]);
        $term = $collaborator->profile->documents()->create([
            'type' => DocumentType::TERM_DATES,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::TERM_DATES->field() . '.jpg'))->id,
        ]);
        $transcript = $collaborator->profile->documents()->create([
            'type' => DocumentType::TRANSCRIPT,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::TRANSCRIPT->field() . '.jpg'))->id,
        ]);
        $visa = $collaborator->profile->documents()->create([
            'type' => DocumentType::VISA,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::VISA->field() . '.jpg'))->id,
        ]);
        $vitae = $collaborator->profile->documents()->create([
            'type' => DocumentType::VITAE,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::VITAE->field() . '.jpg'))->id,
        ]);

        InlineDownload::apply();

        $route = route('collaborate.profile.document.stream', ['document' => $passport->id]);

        $this->validateAuthGuard($route);

        $this->signIn();

        $this->validateCollaborateRole($route);

        $mapping = [
            DocumentType::CONTRACT->field() => $contract,
            DocumentType::IDENTIFICATION->field() => $passport,
            DocumentType::SCHEDULE_A->field() => $schedule,
            DocumentType::TERM_DATES->field() => $term,
            DocumentType::TRANSCRIPT->field() => $transcript,
            DocumentType::VISA->field() => $visa,
            DocumentType::VITAE->field() => $vitae,
        ];

        collect(array_keys($mapping))
            ->each(function ($type) use ($mapping) {
                $route = route('collaborate.profile.document.stream', ['document' => $mapping[$type]]);

                $this->get($route)
                    ->assertSuccessful()
                    ->assertInlineDownload("{$type}.jpg");

                $route = route('collaborate.profile.document.download', ['document' => $mapping[$type]]);

                $this->get($route)
                    ->assertSuccessful()
                    ->assertDownload("{$type}.jpg");
            });

        $profile = $collaborator->profile;

        $payload = [
            'subtype' => IdentityDocumentType::PASSPORT->value,
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
        ];

        $this->assertNull($passport->subtype);

        $route = route('collaborate.profile.document.validate', ['document' => $passport->id]);

        $this->put($route, $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame(IdentityDocumentType::PASSPORT->value, $passport->refresh()->subtype);
        $this->assertSame(now()->addYear()->toDateString(), $passport->valid_to->toDateString());

        $payload = [
            'subtype' => IdentityDocumentType::ID->value,
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
        ];

        $this->put($route, $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame(IdentityDocumentType::ID->value, $passport->refresh()->subtype);
        $this->assertSame(now()->addYear()->toDateString(), $passport->valid_to->toDateString());

        $payload = [
            'restricted_visa' => 0,
            'restriction_type' => $this->faker->words(4, true),
            'visa_hours' => 5,
            'visa_available_hours' => 5,
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
        ];

        $route = route('collaborate.profile.document.validate', ['document' => $visa->id]);
        $this->followingRedirects()
            ->put($route, $payload)
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $profile->refresh();

        $this->assertSame(false, $profile->restricted_visa);
        $this->assertSame(now()->addYear()->toDateString(), $visa->refresh()->valid_to->toDateString());
        $this->assertNull($profile->restriction_type);
        $this->assertNull($profile->visa_hours);
        $this->assertNull($profile->visa_available_hours);

        $payload = [
            'restricted_visa' => 1,
            'restriction_type' => $this->faker->words(4, true),
            'visa_hours' => 5,
            'visa_available_hours' => 5,
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
        ];

        $this->followingRedirects()
            ->put($route, $payload)
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $profile->refresh();

        $this->assertSame(true, $profile->restricted_visa);
        $this->assertSame(now()->addYear()->toDateString(), $visa->refresh()->valid_to->toDateString());
        $this->assertSame($payload['restriction_type'], $profile->restriction_type);
        $this->assertSame(5, $profile->visa_hours);
        $this->assertSame(5, $profile->visa_available_hours);

        $payload = [
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
        ];

        $route = route('collaborate.profile.document.validate', ['document' => $vitae->id]);

        $this->put($route, $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertNotNull($vitae->refresh()->valid_to);
    }
}
