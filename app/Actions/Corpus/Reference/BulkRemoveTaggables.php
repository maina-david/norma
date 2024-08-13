<?php

namespace App\Actions\Corpus\Reference;

use App\Enums\Corpus\MetaChangeStatus;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkRemoveTaggables
{
    use AsAction;
    use GathersReferenceIdsTrait;
    use TaggablePivotColumnTrait;

    /**
     * @param array<int>     $referenceIds
     * @param array<int>     $taggableIds
     * @param string         $pivotClass
     * @param string         $pivotDraftClass
     * @param User|null|null $user
     *
     * @return void
     */
    public function handle(
        array $referenceIds,
        array $taggableIds,
        string $pivotClass,
        string $pivotDraftClass,
        ?User $user = null
    ): void {
        $refsToTag = $this->gatherReferenceIds($referenceIds);

        $pivotTable = (new $pivotClass())->getTable(); // @phpstan-ignore-line
        $draftsTable = (new $pivotDraftClass())->getTable(); // @phpstan-ignore-line
        $pivotColumn = $this->getPivotColumn($pivotDraftClass);
        $userStr = $user ? $user->id . ' as ' : '';

        $now = now()->format('Y-m-d H:i:s');
        foreach ($taggableIds as $taggableId) {
            foreach (collect($refsToTag)->chunk(1000) as $refIds) {
                $inserts = [];
                // if there are drafts for additions, change those to removals
                DB::table($draftsTable)
                    ->whereIn('reference_id', $refIds)
                    ->where($pivotColumn, $taggableId)
                    ->where('change_status', MetaChangeStatus::ADD->value)
                    ->update([
                        'change_status' => MetaChangeStatus::REMOVE->value,
                    ]);

                $vars = $refIds->join(',');

                $sql = "INSERT INTO `{$draftsTable}` (`reference_id`, `{$pivotColumn}`, `annotation_source_id`, `applied_by`, `applied_at`, `change_status`)
                        SELECT `reference_id`, `{$pivotColumn}`, NULL as `annotation_source_id`, {$userStr}`applied_by`, NOW() as `applied_at`, " . MetaChangeStatus::REMOVE->value . " as `change_status` FROM `{$pivotTable}`
                            WHERE `reference_id` IN ({$vars})
                            AND `{$pivotColumn}` = {$taggableId}
                        ON DUPLICATE KEY UPDATE `change_status` = " . MetaChangeStatus::REMOVE->value . ';
                    ';
                // not using Eloquent so we don't trigger events and slow this down
                DB::statement($sql);
            }
        }
    }
}
