<?php

namespace App\Actions\Corpus\Reference;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use App\Models\Corpus\TocItem;
use App\Services\Corpus\TocItemContentExtractor;
use App\Services\HTMLPurifierService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkInsertNewReferencesFromTocItems
{
    use AsAction;

    public function __construct(
        protected TocItemContentExtractor $tocItemContentExtractor,
        protected HTMLPurifierService $htmlPurifier
    ) {
    }

    /**
     * Generate references from the toc collection.
     *
     * @param array<int, int> $tocItemIds
     * @param int             $workId
     * @param Reference|null  $belowReference
     *
     * @throws Exception
     *
     * @return array<int, Reference>
     */
    public function handle(array $tocItemIds, int $workId, ?Reference $belowReference = null): array
    {
        $items = TocItem::with(['contentResource'])
            ->whereKey($tocItemIds)
            ->whereNotExists(function (Builder $query) use ($workId) {
                $refInstance = new Reference();

                $query->select(DB::raw(1))
                    ->from($refInstance->getTable())
                    ->whereNull('deleted_at')
                    ->where('work_id', $workId)
                    ->whereColumn($refInstance->qualifyColumn('uid'), (new TocItem())->qualifyColumn('uid'));
            })
            ->orderBy('doc_position')
            ->get();

        $position = $this->getInitialPosition($belowReference, $workId, $items->count());

        /** @var Reference $workRef */
        $workRef = Reference::where('work_id', $workId)->typeWork()->first();

        $created = [];

        foreach ($items as $tocItem) {
            $created[] = $this->generateReference($tocItem, $workRef, $workId, $position);
            $position++;
        }

        return $created;
    }

    /**
     * Get the initial position to use.
     *
     * @param \App\Models\Corpus\Reference|null $belowReference
     * @param int                               $workId
     * @param int                               $moveBy
     *
     * @return int|mixed|null
     */
    protected function getInitialPosition(?Reference $belowReference, int $workId, int $moveBy): mixed
    {
        if ($belowReference) {
            // move all refs after one position up
            Reference::where('work_id', $belowReference->work_id)
                ->where('position', '>', $belowReference->position ?? 0)
                ->update(['position' => DB::raw("position + {$moveBy}")]);

            return $belowReference->position + 1;
        }

        return Reference::where('work_id', $workId)->max('position') + 1;
    }

    /**
     * @param \App\Models\Corpus\TocItem   $tocItem
     * @param \App\Models\Corpus\Reference $workRef
     * @param int                          $workId
     * @param mixed                        $position
     *
     * @throws Exception
     *
     * @return \App\Models\Corpus\Reference
     */
    protected function generateReference(TocItem $tocItem, Reference $workRef, int $workId, mixed $position): Reference
    {
        /** @var Reference $newRef */
        $newRef = Reference::create([
            'uid' => $tocItem->uid,
            'parent_id' => $workRef->id,
            'work_id' => $workId,
            'status' => ReferenceStatus::pending()->value,
            'type' => ReferenceType::citation()->value,
            'volume' => 1,
            'level' => 1,
            'start' => $position,
            'position' => $position,
        ]);

        $content = $this->tocItemContentExtractor->extractItem($tocItem);
        $content = $this->htmlPurifier->cleanSection($content);

        ReferenceContentDraft::create([
            'reference_id' => $newRef->id,
            'title' => is_null($tocItem->label) ? null : trim($tocItem->label),
            'html_content' => $content,
        ]);

        return $newRef;
    }
}
