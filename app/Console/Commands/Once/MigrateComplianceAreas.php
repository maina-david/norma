<?php

namespace App\Console\Commands\Once;

use App\Enums\Assess\AssessmentItemType;
use App\Enums\Ontology\CategoryType;
use App\Models\Actions\ActionArea;
use App\Models\Actions\Pivots\ActionAreaReference;
use App\Models\Actions\Pivots\ActionAreaReferenceDraft;
use App\Models\Assess\AssessmentItem;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;

/** @codeCoverageIgnore */
class MigrateComplianceAreas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:migrate-compliance-areas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate compliance areas to action areas.';

    /** @var array<int, array<int, int>> */
    protected array $failed = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->migrateAssessmentItems();
        $this->migrateAssessmentItemReferences();

        return 0;
    }

    /**
     * @return void
     */
    protected function migrateAssessmentItems(): void
    {
        AssessmentItem::where('type', AssessmentItemType::COMPLIANCE->value)
            ->active()
            ->with(['categories'])
            ->chunkById(100, function ($items) {
                $toInsert = [];

                foreach ($items as $item) {
                    /** @var AssessmentItem $item */
                    $subject = $item->categories->where('category_type_id', CategoryType::SUBJECT->value)->first();
                    $control = $item->categories->where('category_type_id', CategoryType::CONTROL->value)->first();
                    $activity = $item->categories->where('category_type_id', CategoryType::ACTIVITY->value)->first();

                    if ((!$subject && !$activity) || !$control) {
                        $this->failed[] = [$item->id];
                        continue;
                    }

                    /** @var \App\Models\Ontology\Category $control */
                    /** @var \App\Models\Ontology\Category $activity */
                    $toInsert[] = [
                        'id' => $item->id,
                        'title' => $item->toDescription(),
                        'control_category_id' => $control->id,
                        'subject_category_id' => $subject->id ?? $activity->id,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];

                    $this->info('Added assessment item ' . $item->id);
                }

                ActionArea::insert($toInsert);
            });

        if (!empty($this->failed)) {
            $this->error('The following have failed.');

            $this->table(['assessment_item_id'], $this->failed);
        }
    }

    /**
     * @return void
     */
    protected function migrateAssessmentItemReferences(): void
    {
        ActionAreaReference::insertOrIgnoreUsing(
            ['action_area_id', 'reference_id', 'applied_by', 'approved_by', 'annotation_source_id', 'applied_at'],
            function (Builder $query) {
                $query->select(['assessment_item_id', 'reference_id', 'applied_by', 'approved_by', 'annotation_source_id', 'applied_at'])
                    ->from('corpus_assessment_item_reference')
                    ->join('corpus_assessment_items', 'corpus_assessment_items.id', '=', 'corpus_assessment_item_reference.assessment_item_id')
                    ->where('corpus_assessment_items.type', AssessmentItemType::COMPLIANCE->value);
            }
        );

        $this->info('Migrated live attachments.');

        ActionAreaReferenceDraft::insertOrIgnoreUsing(
            ['action_area_id', 'reference_id', 'change_status', 'applied_by', 'approved_by', 'annotation_source_id', 'applied_at'],
            function (Builder $query) {
                $query->select(['assessment_item_id', 'reference_id', 'change_status', 'applied_by', 'approved_by', 'annotation_source_id', 'applied_at'])
                    ->from('assessment_item_reference_draft')
                    ->join('corpus_assessment_items', 'corpus_assessment_items.id', '=', 'assessment_item_reference_draft.assessment_item_id')
                    ->where('corpus_assessment_items.type', AssessmentItemType::COMPLIANCE->value);
            }
        );

        $this->info('Migrated draft attachments.');
    }
}
