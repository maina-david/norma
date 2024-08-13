<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\DocumentType;
use App\Models\Workflows\Document;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDocument implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): mixed
    {
        return Document::create([
            'title' => $requestData['title'],
            'document_type' => DocumentType::GENERIC->value,
        ]);
    }
}
