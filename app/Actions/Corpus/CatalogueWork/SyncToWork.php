<?php

namespace App\Actions\Corpus\CatalogueWork;

use App\Models\Corpus\CatalogueWork;
use App\Models\Corpus\CatalogueWorkExpression;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Stores\Corpus\ReferenceRequirementsCollectionStore;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncToWork
{
    use AsAction;

    public function __construct(protected ReferenceRequirementsCollectionStore $referenceRequirementsCollectionStore)
    {
    }

    public function handle(CatalogueWork $catalogueWork): Work
    {
        return $this->syncWork($catalogueWork);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int $catalogueWorkId
     *
     * @return void
     */
    public function asJob(int $catalogueWorkId): void
    {
        /** @var CatalogueWork */
        $catalogueWork = CatalogueWork::findOrFail($catalogueWorkId);
        $this->handle($catalogueWork);
    }

    /**
     * @param CatalogueWork $cWork
     *
     * @return Work
     */
    public function syncWork(CatalogueWork $cWork): Work
    {
        $newCreation = false;
        $work = $cWork->work;
        if (is_null($work)) {
            $work = $this->createWorkFromCatalogue($cWork);
            $newCreation = true;
            $cWork->update(['work_id' => $work->id]);
        }
        /** @var Work $work */
        $lastExpression = null;
        foreach ($cWork->expressions as $expression) {
            $lastExpression = $this->syncExpression($expression, $work->id);
        }

        if ($newCreation && !is_null($lastExpression)) {
            $work->update(['active_work_expression_id' => $lastExpression->id]);
        }

        /** @var Work */
        return $work;
    }

    /**
     * @param CatalogueWork $cWork
     *
     * @return Work
     */
    public function createWorkFromCatalogue(CatalogueWork $cWork): Work
    {
        $data = [
            'catalogue_work_id' => $cWork->id,
            'source_id' => $cWork->source_id,
            'title' => $cWork->title,
            'title_translation' => $cWork->title_translation,
            'work_number' => $cWork->work_number ?? null,
            'work_type' => $cWork->work_type ?? null,
            'work_date' => $cWork->work_date ?? null,
            'language_code' => $cWork->language_code,
            'source_url' => $cWork->start_url,
            'notice_number' => $cWork->publication_document_number ?? null,
            'primary_location_id' => $cWork->primary_location_id,
            'source_unique_id' => $cWork->source_unique_id ?? null,
            'gazette_number' => $cWork->publication_number ?? null,
            'effective_date' => $cWork->effective_date,
            // 'issuing_authority' => $cWork->issuing_authority_id,
        ];

        /** @var Work */
        $work = Work::create($data);

        if ($work->primary_location_id) {
            /** @var Reference */
            $workReference = $work->workReference;
            $this->referenceRequirementsCollectionStore->attachRelations($workReference, 'requirementsCollections', collect([$work->primary_location_id]));
        }

        return $work;
    }

    /**
     * @param CatalogueWorkExpression $cExpression
     * @param int                     $workId
     *
     * @return WorkExpression
     */
    public function syncExpression(CatalogueWorkExpression $cExpression, int $workId): WorkExpression
    {
        $data = [
            'work_id' => $workId,
            'start_date' => $cExpression->start_date,
            'source_document_id' => $cExpression->source_document_id,
            'converted_document_id' => $cExpression->converted_document_id,
            'volume' => $cExpression->volumes,
            'show_source_document' => false,
        ];

        /** @var WorkExpression */
        $expression = WorkExpression::firstOrNew(['work_id' => $workId, 'start_date' => $cExpression->start_date]);
        $expression->fill($data);
        $expression->save();

        return $expression;
    }
}
