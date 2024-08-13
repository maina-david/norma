<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\DocumentType;
use App\Models\Arachno\Source;
use App\Models\Workflows\Document;
use Lorisleiva\Actions\Concerns\AsAction;

class SelectMonitorSource implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): Document
    {
        /** @var Source $source */
        $source = Source::find($requestData['source_id']);

        /** @var Document $document */
        $document = Document::create([
            'title' => $source->title,
            'document_type' => DocumentType::SOURCE_MONITORING->value,
            'source_id' => $source->id,
        ]);

        return $document;
    }
}
