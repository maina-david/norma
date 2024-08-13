<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\DocumentType;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Document;
use Lorisleiva\Actions\Concerns\AsAction;

class SelectWork implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): Document
    {
        /** @var WorkExpression $expression */
        $expression = WorkExpression::with(['work'])->find($requestData['work_expression_id']);

        /** @var Document $document */
        $document = Document::where('work_expression_id', $expression->id)->firstOrNew();

        $document->fill([
            'title' => $expression->work->title,
            'document_type' => DocumentType::LEGISLATION->value,
            'work_id' => $expression->work_id,
            'work_expression_id' => $expression->id,
        ]);

        $document->save();

        return $document;
    }
}
