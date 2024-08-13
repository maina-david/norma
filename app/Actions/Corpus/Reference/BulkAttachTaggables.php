<?php

namespace App\Actions\Corpus\Reference;

use App\Enums\Corpus\MetaChangeStatus;
use App\Models\Actions\Pivots\ActionAreaReferenceDraft;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Auth\User;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkAttachTaggables
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
        $refsToTag = $this->gatherReferenceIds($referenceIds, $this->shouldInherit($pivotDraftClass));

        $draftsTable = (new $pivotDraftClass())->getTable(); // @phpstan-ignore-line
        $pivotColumn = $this->getPivotColumn($pivotDraftClass);

        // then we'll add the new ones
        $now = now()->format('Y-m-d H:i:s');
        foreach ($taggableIds as $taggableId) {
            foreach (collect($refsToTag)->chunk(1000) as $refIds) {
                $inserts = [];
                // if there are drafts for removals, change those to additions by deleting them and adding back in drafts
                DB::table($draftsTable)
                    ->whereIn('reference_id', $refIds)
                    ->where($pivotColumn, $taggableId)
                    ->where('change_status', MetaChangeStatus::REMOVE->value)
                    ->delete();

                foreach ($refIds as $refId) {
                    $inserts[] = [
                        'reference_id' => $refId,
                        $pivotColumn => $taggableId,
                        'change_status' => MetaChangeStatus::ADD->value,
                        'applied_by' => $user?->id ?? null,
                        'applied_at' => $now,
                    ];
                }
                // not using Eloquent so we don't trigger events and slow this down
                DB::table($draftsTable)->insertOrIgnore($inserts);
            }
        }
    }

    protected function shouldInherit(string $pivotDraftClass): bool
    {
        return match ($pivotDraftClass) {
            ContextQuestionReferenceDraft::class,
            AssessmentItemReferenceDraft::class,
            ActionAreaReferenceDraft::class => false,
            default => true,
        };
    }
}
