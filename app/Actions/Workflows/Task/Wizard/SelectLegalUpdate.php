<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\DocumentType;
use App\Models\Notify\LegalUpdate;
use App\Models\Workflows\Document;
use Lorisleiva\Actions\Concerns\AsAction;

class SelectLegalUpdate implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): Document
    {
        /** @var LegalUpdate $update */
        $update = LegalUpdate::find($requestData['legal_update_id']);

        $domains = $requestData['domains'] ?? [];

        unset($requestData['content_resource_file'], $requestData['domains'], $requestData['legal_update_id']);

        $update->update($requestData);

        // @codeCoverageIgnoreStart
        if (!empty($domains)) {
            $update->legalDomains()->sync($domains);
        }
        // @codeCoverageIgnoreEnd

        /** @var Document $document */
        $document = Document::where('legal_update_id', $update->id)->firstOrNew();

        $document->forceFill([
            'title' => $update->title,
            'document_type' => DocumentType::LEGAL_UPDATE->value,
            'legal_update_id' => $update->id,
        ]);

        $document->save();

        return $document;
    }
}
