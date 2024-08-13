<?php

namespace App\Actions\Notify\LegalUpdate;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Work;
use App\Models\Notify\LegalUpdate;
use App\Services\Storage\WorkStorageProcessor;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * Just used temporarily while migrating updates.
 *
 * @codeCoverageIgnore
 */
class SyncFromWork
{
    use AsAction;

    public function asJob(int $updateId): void
    {
        /** @var LegalUpdate */
        $update = LegalUpdate::findOrFail($updateId);
        $this->handle($update);
    }

    public function handle(LegalUpdate $update): void
    {
        /** @var Work */
        $work = $update->work;
        $contentResource = $this->sourceFileToContentResource($work);
        $update->update([
            'status' => LegalUpdatePublishedStatus::PUBLISHED->value,
            'primary_location_id' => $work->primary_location_id,
            'title_translation' => $work->title_translation,
            'highlights' => $work->highlights,
            'update_report' => $work->update_report,
            'changes_to_register' => $work->changes_to_register,
            'source_id' => $work->source_id,
            'source_url' => $work->source_url,
            'language_code' => $work->language_code,
            'work_number' => $work->work_number,
            'publication_number' => $work->gazette_number,
            'publication_document_number' => $work->notice_number,
            'publication_date' => $work->work_date,
            'effective_date' => $work->effective_date,
            'content_resource_id' => $contentResource?->id,
        ]);
        try {
            if ($work->workReference?->legalDomains->isNotEmpty()) {
                $update->legalDomains()->sync($work->workReference->legalDomains->modelKeys());
            }
        } catch (Throwable $th) {
        }
    }

    /**
     * Copies over the source file to a content resource.
     *
     * @param Work $work
     *
     * @return ContentResource|null
     */
    private function sourceFileToContentResource(Work $work): ?ContentResource
    {
        $currentExpression = $work->getCurrentExpression();

        if (!$currentExpression || !$currentExpression->sourceDocument) {
            return null;
        }

        $path = $currentExpression->sourceDocument->path;
        $disk = WorkStorageProcessor::disk();
        $fileContents = Storage::disk($disk)->get($path);

        if (!$fileContents) {
            return null;
        }

        $contentResource = app(ContentResourceStore::class)->storeResource($fileContents, $currentExpression->sourceDocument->mime_type);

        return $contentResource;
    }
}
