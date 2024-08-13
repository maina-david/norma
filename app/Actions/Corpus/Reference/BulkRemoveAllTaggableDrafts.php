<?php

namespace App\Actions\Corpus\Reference;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkRemoveAllTaggableDrafts
{
    use AsAction;
    use GathersReferenceIdsTrait;

    /**
     * @param array<int> $referenceIds
     * @param string     $pivotDraftClass
     *
     * @return void
     */
    public function handle(
        array $referenceIds,
        string $pivotDraftClass
    ): void {
        $refsToTag = $this->gatherReferenceIds($referenceIds);

        $draftsTable = (new $pivotDraftClass())->getTable(); // @phpstan-ignore-line
        foreach (collect($refsToTag)->chunk(1000) as $refIds) {
            DB::table($draftsTable)
                ->whereIn('reference_id', $refIds)
                ->delete();
        }
    }
}
