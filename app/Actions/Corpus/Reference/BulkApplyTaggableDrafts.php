<?php

namespace App\Actions\Corpus\Reference;

use App\Enums\Corpus\MetaChangeStatus;
use App\Enums\Lookups\AnnotationSourceType;
use App\Models\Actions\ActionArea;
use App\Models\Actions\Pivots\ActionAreaReference;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\Pivots\AssessmentItemCategory;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Auth\User;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Ontology\Pivots\CategoryContextQuestion;
use App\Models\Ontology\Pivots\CategoryReference;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkApplyTaggableDrafts
{
    use AsAction;
    use GathersReferenceIdsTrait;
    use TaggablePivotColumnTrait;

    /**
     * @param array<int> $referenceIds
     * @param string     $pivotClass
     * @param string     $pivotDraftClass
     * @param User|null  $user
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
        foreach (collect($refsToTag)->chunk(1000) as $refIds) {
            DB::transaction(function () use ($refIds, $pivotTable, $draftsTable, $pivotColumn, $user, $pivotClass) {
                $removals = DB::table($draftsTable)
                    ->whereIn('reference_id', $refIds)
                    ->where('change_status', MetaChangeStatus::REMOVE->value)
                    ->get();

                $additions = DB::table($draftsTable)
                    ->whereIn('reference_id', $refIds)
                    ->where('change_status', MetaChangeStatus::ADD->value)
                    ->pluck('reference_id')
                    ->toArray();

                if ($removals->isNotEmpty()) {
                    $removalIds = $removals->pluck('reference_id')->toArray();
                    $grouped = [];
                    foreach ($removals as $remove) {
                        if (!isset($grouped[$remove->{$pivotColumn}])) {
                            $grouped[$remove->{$pivotColumn}] = [];
                        }
                        $grouped[$remove->{$pivotColumn}][] = $remove->reference_id;
                    }
                    // have to group the removals by the taggable ID, as for some references certain taggables are removed and for others it might
                    // be different ones
                    foreach (array_keys($grouped) as $taggableId) {
                        // $removalIds = $removals->filter(fn ($remove) => $remove->{$pivotColumn} === $taggableId)->pluck('reference_id')->toArray();
                        DB::table($pivotTable)
                            ->whereIn('reference_id', $grouped[$taggableId])
                            ->where($pivotColumn, $taggableId)
                            ->delete();
                    }

                    DB::table($draftsTable)
                        ->whereIn('reference_id', $removalIds)
                        ->where('change_status', MetaChangeStatus::REMOVE->value)
                        ->delete();
                }

                if (!empty($additions)) {
                    $additionsStr = [];
                    foreach ($additions as $_) {
                        $additionsStr[] = '?';
                    }
                    $additionsStr = implode(',', $additionsStr);
                    $sql = "INSERT IGNORE INTO `{$pivotTable}` (`reference_id`, `{$pivotColumn}`, `annotation_source_id`, `applied_by`, `applied_at`, `approved_by`)
                    SELECT `reference_id`, `{$pivotColumn}`, `annotation_source_id`, `applied_by`, `applied_at`, " . ($user ? '?' : 'NULL') . " as `approved_by` FROM `{$draftsTable}`
                        WHERE `reference_id` IN ({$additionsStr})
                ";

                    if ($additionsStr !== '') {
                        $bindings = $user ? [$user->id, ...$additions] : $additions;
                        DB::statement($sql, $bindings);
                        $this->handleAutoCategories($pivotClass, $additionsStr, $additions);
                        $this->handleActionAreasAutoCategories($pivotClass, $additionsStr, $additions);
                        $this->autoAttachActionAreas($pivotClass, $additionsStr, $additions);
                        $this->autoAttachAssessmentItems($pivotClass, $additionsStr, $additions);
                    }
                }

                DB::table($draftsTable)
                    ->whereIn('reference_id', $refIds)
                    ->delete();
            });
        }
    }

    /**
     * @param string     $pivotClass
     * @param string     $additionsStr
     * @param array<int> $additions
     *
     * @return void
     */
    protected function handleAutoCategories(
        string $pivotClass,
        string $additionsStr,
        array $additions
    ): void {
        if (!in_array($pivotClass, [ContextQuestionReference::class, AssessmentItemReference::class])) {
            return;
        }

        $annotationSourceType = $pivotClass === AssessmentItemReference::class ? AnnotationSourceType::ASSESSMENT_ITEM->value : AnnotationSourceType::CONTEXT_QUESTION->value;
        $categoryPivotClass = $pivotClass === AssessmentItemReference::class ? AssessmentItemCategory::class : CategoryContextQuestion::class;
        $pivotTable = (new $pivotClass())->getTable(); // @phpstan-ignore-line
        $relatedCategoryPivotTable = (new $categoryPivotClass())->getTable();
        $categoryPivotTable = (new CategoryReference())->getTable();
        $categoryPivotColumn = $pivotClass === AssessmentItemReference::class ? 'assessment_item_id' : 'context_question_id';
        $sql = "	
            INSERT IGNORE INTO `{$categoryPivotTable}` (`category_id`, `reference_id`, `annotation_source_id`, `applied_at`)
                SELECT `category_id`, `reference_id`, {$annotationSourceType} as  `annotation_source_id`, NOW() as `applied_at` FROM `{$pivotTable}` qr
                LEFT JOIN `{$relatedCategoryPivotTable}` ccq on ccq.`{$categoryPivotColumn}` = qr.`{$categoryPivotColumn}`
            WHERE 
                qr.`reference_id` IN ({$additionsStr});
        ";
        DB::statement($sql, $additions);
    }

    /**
     * Add categories from action areas.
     *
     * @param string            $pivotClass
     * @param string            $additionsStr
     * @param array<int, mixed> $additions
     *
     * @return void
     */
    protected function handleActionAreasAutoCategories(string $pivotClass, string $additionsStr, array $additions): void
    {
        if ($pivotClass === ActionAreaReference::class) {
            $insertInto = (new CategoryReference())->getTable();
            $type = AnnotationSourceType::ACTION_AREA->value;
            $actions = (new ActionArea())->getTable();
            $pivotTable = (new ActionAreaReference())->getTable();

            $columns = ['subject_category_id', 'control_category_id'];

            foreach ($columns as $column) {
                $fromPivot = "
                SELECT `{$column}`, `reference_id`, {$type} as `annotation_source_id`, NOW() as `applied_at` FROM `{$pivotTable}` pt
                INNER JOIN `{$actions}` act on act.id = pt.action_area_id
                ";

                $sql = "	
                INSERT IGNORE INTO `{$insertInto}` (`category_id`, `reference_id`, `annotation_source_id`, `applied_at`)
                    {$fromPivot}
                WHERE 
                    pt.`reference_id` IN ({$additionsStr});
                ";

                DB::statement($sql, $additions);
            }
        }
    }

    /**
     * Auto Attach action areas.
     *
     * @param string            $pivotClass
     * @param string            $additionsStr
     * @param array<int, mixed> $additions
     *
     * @return void
     */
    protected function autoAttachActionAreas(string $pivotClass, string $additionsStr, array $additions): void
    {
        if ($pivotClass === AssessmentItemReference::class) {
            $insertInto = (new ActionAreaReference())->getTable();
            $type = AnnotationSourceType::ASSESSMENT_ITEM->value;
            $join = (new AssessmentItem())->getTable();
            $from = (new AssessmentItemReference())->getTable();

            $fromPivot = "
            SELECT `action_area_id`, `reference_id`, {$type} as `annotation_source_id`, NOW() as `applied_at` FROM `{$from}` pt
            INNER JOIN `{$join}` ass on ass.id = pt.assessment_item_id
            ";

            $sql = "	
            INSERT IGNORE INTO `{$insertInto}` (`action_area_id`, `reference_id`, `annotation_source_id`, `applied_at`)
                {$fromPivot}
            WHERE 
                pt.`reference_id` IN ({$additionsStr});
            ";

            DB::statement($sql, $additions);
        }
    }

    /**
     * @param string            $pivotClass
     * @param string            $additionsStr
     * @param array<int, mixed> $additions
     *
     * @return void
     */
    protected function autoAttachAssessmentItems(string $pivotClass, string $additionsStr, array $additions): void
    {
        if ($pivotClass === ActionAreaReference::class) {
            /** @var array<int, int> $inherit */
            $inherit = config('assess.auto_apply_from_action_areas.locations.inherit', []);
            /** @var array<int, int> $exclusive */
            $exclusive = config('assess.auto_apply_from_action_areas.locations.exclusive', []);
            /** @var Work $work */
            $work = Work::whereIn('id', Reference::whereKey($additions[0])->select(['work_id']))->first();

            if ((!empty($inherit) || !empty($exclusive)) && ($work->primary_location_id ?? false)) {
                $location = Location::with(['ancestorsWithSelfUnordered:id'])->where('id', $work->primary_location_id)->first();
                $ancestors = $location ? $location->ancestorsWithSelfUnordered->pluck('id')->all() : [];

                if (!empty(array_intersect($inherit, $ancestors)) || in_array($work->primary_location_id, $exclusive)) {
                    $this->handleAttachAssessmentItemsFromActions($pivotClass, $additionsStr, $additions);
                }
            }
        }
    }

    /**
     * @param string            $pivotClass
     * @param string            $additionsStr
     * @param array<int, mixed> $additions
     *
     * @return void
     */
    protected function handleAttachAssessmentItemsFromActions(string $pivotClass, string $additionsStr, array $additions): void
    {
        $insertInto = (new AssessmentItemReferenceDraft())->getTable();
        $type = AnnotationSourceType::ACTION_AREA->value;
        $join = (new AssessmentItem())->getTable();
        $from = (new ActionAreaReference())->getTable();

        $fromPivot = "
            SELECT `id`, `reference_id`, {$type} as `annotation_source_id`, NOW() as `applied_at` FROM `{$from}` pt
            INNER JOIN `{$join}` ass on ass.action_area_id = pt.action_area_id
            ";

        $sql = "	
            INSERT IGNORE INTO `{$insertInto}` (`assessment_item_id`, `reference_id`, `annotation_source_id`, `applied_at`)
                {$fromPivot}
            WHERE 
                pt.`reference_id` IN ({$additionsStr});
            ";

        DB::statement($sql, $additions);
    }
}
