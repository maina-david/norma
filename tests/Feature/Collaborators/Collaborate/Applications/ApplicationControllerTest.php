<?php

namespace Tests\Feature\Collaborators\Collaborate\Applications;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Enums\Collaborators\ContractType;
use App\Enums\Collaborators\DocumentArchiveOptions;
use App\Enums\Collaborators\DocumentType;
use App\Enums\Storage\FileType;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use App\Models\Storage\Collaborate\Attachment;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Abstracts\CrudTestCase;
use Tests\Macros\InlineDownload;

class ApplicationControllerTest extends CrudTestCase
{
    protected bool $descending = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return CollaboratorApplication::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborator-applications';
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleFields(): array
    {
        return ['email'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleLabels(): array
    {
        return ['Email'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function excludeActions(): array
    {
        return ['create', 'store', 'edit', 'update'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        return parent::preCreate($factory, $route)->has(Profile::factory()->state(['user_id' => null]), 'profile');
    }

    public function testApprovalAndDenial(): void
    {
        $this->signIn($this->collaborateSuperUser());

        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()->has(Profile::factory()->state(['user_id' => null]), 'profile')
            ->create(['stage' => ApplicationStage::one()]);

        $this->post(route('collaborate.collaborator-applications.approve', ['application' => $application->id]))
            ->assertRedirect();

        $this->assertDatabaseHas(CollaboratorApplication::class, ['id' => $application->id, 'application_state' => ApplicationStatus::approved()->value]);

        $application->update(['stage' => ApplicationStage::two()->value, 'application_state' => ApplicationStatus::pending()->value]);

        $this->post(route('collaborate.collaborator-applications.approve', ['application' => $application->id]))
            ->assertRedirect();

        $this->assertDatabaseHas(CollaboratorApplication::class, ['id' => $application->id, 'application_state' => ApplicationStatus::approved()->value]);

        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()->has(Profile::factory()->state(['user_id' => null]), 'profile')
            ->create(['stage' => ApplicationStage::one()]);

        $this->delete(route('collaborate.collaborator-applications.decline', ['application' => $application->id]))
            ->assertRedirect();

        $this->assertDatabaseHas(CollaboratorApplication::class, ['id' => $application->id, 'application_state' => ApplicationStatus::declined()->value]);

        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()->has(Profile::factory()->state(['user_id' => null]), 'profile')
            ->create(['stage' => ApplicationStage::one(), 'application_state' => ApplicationStatus::declined()->value]);

        $this->post(route('collaborate.collaborator-applications.revert', ['application' => $application->id]))
            ->assertRedirect();

        $this->assertDatabaseHas(CollaboratorApplication::class, ['id' => $application->id, 'application_state' => ApplicationStatus::pending()->value]);
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItSavesTheContract(): void
    {
        Storage::fake();

        $type = FileType::collaborators();
        $file = UploadedFile::fake()->image('contract.jpg');
        $attachment = new Attachment();
        $attachment->saveFile($file);

        Storage::assertExists(config("filesystems.attachments.{$type}.path"));
        Storage::assertExists($attachment->path);

        $this->signIn($this->collaborateSuperUser());

        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()->has(Profile::factory()->state(['user_id' => null]), 'profile')->create(['stage' => ApplicationStage::one()]);

        $payload = [
            'reason_for_update' => DocumentArchiveOptions::NEW_REQUIREMENTS->value,
            'contract' => UploadedFile::fake()->image('contract.jpg'),
            'contract_signed' => true,
            'contract_type' => ContractType::zeroHour()->value,
        ];

        $this->put(route('collaborate.profile.document.upload', ['type' => DocumentType::CONTRACT->field(), 'profile' => $application->profile->id]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();
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

        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()->has(Profile::factory()->state(['user_id' => null]), 'profile')->create(['stage' => ApplicationStage::one()]);

        $contract = $application->profile->documents()->create([
            'type' => DocumentType::CONTRACT,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::CONTRACT->field() . '.jpg'))->id,
        ]);
        $passport = $application->profile->documents()->create([
            'type' => DocumentType::IDENTIFICATION,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::IDENTIFICATION->field() . '.jpg'))->id,
        ]);
        $transcript = $application->profile->documents()->create([
            'type' => DocumentType::TRANSCRIPT,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::TRANSCRIPT->field() . '.jpg'))->id,
        ]);
        $visa = $application->profile->documents()->create([
            'type' => DocumentType::VISA,
            'attachment_id' => (new Attachment())->saveFile(UploadedFile::fake()->image(DocumentType::VISA->field() . '.jpg'))->id,
        ]);
        $vitae = $application->profile->documents()->create([
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
            DocumentType::TRANSCRIPT->field() => $transcript,
            DocumentType::VISA->field() => $visa,
            DocumentType::VITAE->field() => $vitae,
        ];

        collect(array_keys($mapping))
            ->each(function ($type) use ($mapping) {
                $route = route('collaborate.profile.document.stream', [
                    'document' => $mapping[$type],
                ]);

                $this->get($route)
                    ->assertSuccessful()
                    ->assertInlineDownload("{$type}.jpg");

                $route = route('collaborate.profile.document.download', [
                    'document' => $mapping[$type],
                ]);

                $this->get($route)
                    ->assertSuccessful()
                    ->assertDownload("{$type}.jpg");
            });
    }

    public function testFilters(): void
    {
        /** @var CollaboratorApplication $application */
        $application = CollaboratorApplication::factory()
            ->has(Profile::factory()->state([
                'user_id' => null,
                'nationality' => 'KE',
                'current_residency' => 'KE',
            ]), 'profile')
            ->create([
                'stage' => ApplicationStage::one()->value,
                'application_state' => ApplicationStatus::pending()->value,
            ]);

        $this->signIn($this->collaborateSuperUser());

        $this->get(route('collaborate.collaborator-applications.index'))
            ->assertSuccessful()
            ->assertSee($application->fname);

        $filters = [
            'stage' => ApplicationStage::one()->value,
            'status' => ApplicationStatus::pending()->value,
            'nationality' => 'KE',
            'residency' => 'ZA',
        ];

        $this->get(route('collaborate.collaborator-applications.index', $filters))
            ->assertSuccessful()
            ->assertDontSee($application->fname);
    }
}
