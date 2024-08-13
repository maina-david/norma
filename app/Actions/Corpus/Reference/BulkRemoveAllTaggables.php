<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkRemoveAllTaggables
{
    use AsAction;
    use GathersReferenceIdsTrait;
    use TaggablePivotColumnTrait;

    /**
     * @param array<int>     $referenceIds
     * @param string         $pivotClass
     * @param string         $pivotDraftClass
     * @param User|null|null $user
     *
     * @return void
     */
    public function handle(
        array $referenceIds,
        string $pivotClass,
        string $pivotDraftClass,
        ?User $user = null
    ): void {
        $refsToTag = $this->gatherReferenceIds($referenceIds);
        $pivotTable = (new $pivotClass())->getTable(); // @phpstan-ignore-line
        $draftsTable = (new $pivotDraftClass())->getTable(); // @phpstan-ignore-line
        $pivotColumn = $this->getPivotColumn($pivotDraftClass);

        $userStr = $user ? $user->id . ' as ' : '';

        foreach (collect($refsToTag)->chunk(500) as $refIds) {
            $vars = $refIds->join(',');

            $sql = "INSERT INTO `{$draftsTable}` (`reference_id`, `{$pivotColumn}`, `annotation_source_id`, `applied_by`, `applied_at`, `change_status`)
                SELECT `reference_id`, `{$pivotColumn}`, NULL as `annotation_source_id`, {$userStr}`applied_by`, NOW() as `applied_at`, 0 as `change_status` FROM `{$pivotTable}`
                    WHERE `reference_id` IN ({$vars})
                ON DUPLICATE KEY UPDATE `change_status` = 0;
            ";
            Log::debug($sql);
            DB::statement($sql);
        }
    }
}
