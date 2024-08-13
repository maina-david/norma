<?php

namespace App\Actions\Corpus\Reference;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkRemoveTaggableDrafts
{
    use AsAction;
    use GathersReferenceIdsTrait;
    use TaggablePivotColumnTrait;

    /**
     * @param array<int> $referenceIds
     * @param array<int> $taggableIds
     * @param string     $pivotDraftClass
     *
     * @return void
     */
    public function handle(
        array $referenceIds,
        array $taggableIds,
        string $pivotDraftClass
    ): void {
        $refsToTag = $this->gatherReferenceIds($referenceIds);

        $draftsTable = (new $pivotDraftClass())->getTable(); // @phpstan-ignore-line
        $pivotColumn = $this->getPivotColumn($pivotDraftClass);
        foreach ($taggableIds as $taggableId) {
            foreach (collect($refsToTag)->chunk(1000) as $refIds) {
                DB::table($draftsTable)
                    ->whereIn('reference_id', $refIds)
                    ->where($pivotColumn, $taggableId)
                    ->delete();
            }
        }
    }
}
