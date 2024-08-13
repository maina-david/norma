<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\DocumentType;
use App\Enums\Workflows\WizardStep;
use App\Models\Notify\LegalUpdate;
use App\Models\Workflows\Document;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Http\File;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateLegalUpdate implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): mixed
    {
        $usable = [
            WizardStep::CREATE_DOCUMENT->value,
            WizardStep::CREATE_WORK->value,
            WizardStep::SELECT_WORK->value,
        ];

        $documents = collect($executed)->filter(fn ($val, $key) => in_array($key, $usable));

        /** @var LegalUpdate $legalUpdate */
        $legalUpdate = LegalUpdate::create($requestData);

        $legalUpdate->legalDomains()->sync($requestData['domains'] ?? []);

        if (isset($requestData['content_resource_file'])) {
            /** @var File $file */
            $file = $requestData['content_resource_file'];
            /** @var string $mime */
            $mime = $file->getMimeType();
            $resource = app(ContentResourceStore::class)->storeResource($file->getContent(), $mime);
            $legalUpdate->update(['content_resource_id' => $resource->id]);
        }

        if (!$documents->isEmpty()) {
            /** @var Document $document */
            $document = $documents->first();
            $document->update(['legal_update_id' => $legalUpdate->id]);

            return $document;
        }

        // @codeCoverageIgnoreStart
        return Document::create([
            'title' => $legalUpdate->title,
            'document_type' => DocumentType::LEGAL_UPDATE->value,
            'legal_update_id' => $legalUpdate->id,
        ]);
        // @codeCoverageIgnoreEnd
    }
}
