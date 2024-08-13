<?php

namespace App\Console\Commands\Once;

use App\Enums\Collaborators\DocumentType;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\ProfileDocument;
use Illuminate\Console\Command;

class MigrateCollaboratorsDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collaborators:migrate-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the current collaborators documents';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Migrating documents.');

        Profile::doesntHave('documents')
            ->where(function ($builder) {
                $builder->whereNotNull('schedule_a_attachment_id')
                    ->orWhereNotNull('term_dates_attachment_id')
                    ->orWhereNotNull('transcript_attachment_id')
                    ->orWhereNotNull('cv_attachment_id')
                    ->orWhereNotNull('passport_attachment_id')
                    ->orWhereNotNull('visa_attachment_id')
                    ->orWhereNotNull('contract_attachment_id');
            })
            ->get()
            ->each(fn (Profile $profile) => $this->migrateDocuments($profile));

        Profile::whereNotNull('hours_restricted')
            ->get()
            ->each(function (Profile $profile) {
                $hours = (int) $profile->hours_restricted;
                $hours = !$hours ? null : $hours;

                $profile->update([
                    'visa_hours' => $hours,
                    'visa_available_hours' => $hours,
                ]);
            });

        return 0;
    }

    /**
     * Migrate the documents from the profile.
     *
     * @param Profile $profile
     *
     * @return void
     */
    protected function migrateDocuments(Profile $profile): void
    {
        if ($profile->schedule_a_attachment_id) {
            $this->createDocument($profile, [
                'attachment_id' => $profile->schedule_a_attachment_id,
                'type' => DocumentType::SCHEDULE_A,
            ]);
        }

        if ($profile->term_dates_attachment_id) {
            $this->createDocument($profile, [
                'attachment_id' => $profile->term_dates_attachment_id,
                'type' => DocumentType::TERM_DATES,
            ]);
        }

        if ($profile->transcript_attachment_id) {
            $this->createDocument($profile, [
                'attachment_id' => $profile->transcript_attachment_id,
                'type' => DocumentType::TRANSCRIPT,
            ]);
        }

        if ($profile->cv_attachment_id) {
            $this->createDocument($profile, [
                'attachment_id' => $profile->cv_attachment_id,
                'type' => DocumentType::VITAE,
            ]);
        }

        if ($profile->passport_attachment_id) {
            $this->createDocument($profile, [
                'attachment_id' => $profile->passport_attachment_id,
                'type' => DocumentType::IDENTIFICATION,
                'subtype' => $profile->identity_document_type,
                'valid_to' => $profile->identity_document_expiry,
                'approved_at' => $profile->passport_checked_at,
            ]);
        }

        if ($profile->visa_attachment_id) {
            $this->createDocument($profile, [
                'attachment_id' => $profile->visa_attachment_id,
                'type' => DocumentType::VISA,
                'valid_to' => $profile->visa_expires_at,
                'approved_at' => $profile->visa_checked_at,
            ]);
        }

        if ($profile->contract_attachment_id) {
            $this->createDocument($profile, [
                'attachment_id' => $profile->contract_attachment_id,
                'type' => DocumentType::CONTRACT,
                'subtype' => $profile->contract_type,
            ]);
        }

        $this->info("Migrated profile {$profile->id}");
    }

    /**
     * Create the document.
     *
     * @param Profile              $profile
     * @param array<string, mixed> $attributes
     *
     * @return void
     */
    protected function createDocument(Profile $profile, array $attributes): void
    {
        ProfileDocument::create([
            'profile_id' => $profile->id,
            ...$attributes,
            'expired' => now()->gte($attributes['valid_to'] ?? now()->addDay()),
            'active' => true,
            'approved_at' => now(),
        ]);
    }
}
