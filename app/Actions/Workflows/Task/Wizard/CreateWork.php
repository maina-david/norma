<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\DocumentType;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Document;
use App\Services\Storage\WorkStorageProcessor;
use Illuminate\Http\File;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWork implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): mixed
    {
        $processor = app(WorkStorageProcessor::class);

        /** @var Work $work */
        $work = Work::create($requestData);

        /** @var WorkExpression $expression */
        $expression = WorkExpression::create([
            'work_id' => $work->id,
            'start_date' => $work->work_date,
            'language_code' => $work->language_code,
            'volume' => 1,
            'word_count' => 0,
        ]);

        $work->update(['active_work_expression_id' => $expression->id]);

        if (isset($requestData['source_document'])) {
            /** @var File $file */
            $file = $requestData['source_document'];

            $document = $processor->documentFromContent($expression, $file->getContent(), mime: $file->getMimeType());
            $expression->update(['source_document_id' => $document->id]);
        }

        return Document::create([
            'title' => $work->title,
            'document_type' => DocumentType::LEGISLATION->value,
            'work_id' => $work->id,
            'work_expression_id' => $expression->id,
        ]);
    }
}
