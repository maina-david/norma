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
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class InsertNewReferenceFromTocItem
{
    use AsAction;

    public function __construct(
        protected TocItemContentExtractor $tocItemContentExtractor,
        protected HTMLPurifierService $htmlPurifier
    ) {
    }

    /**
     * @param TocItem        $tocItem
     * @param int            $workId
     * @param Reference|null $belowReference
     *
     * @throws Exception
     *
     * @return Reference
     */
    public function handle(TocItem $tocItem, int $workId, ?Reference $belowReference = null): Reference
    {
        $exists = null;

        if ($tocItem->uid) {
            /** @var Reference|null $exists */
            $exists = Reference::where('uid', $tocItem->uid)
                ->where('work_id', $workId)
                ->first();
        }

        if ($exists) {
            return $exists;
        }

        if ($belowReference) {
            // move all refs after one position up
            Reference::where('work_id', $belowReference->work_id)
                ->where('position', '>', $belowReference->position ?? 0)
                ->update(['position' => DB::raw('position + 1')]);

            $position = $belowReference->position + 1;
        } else {
            $position = Reference::where('work_id', $workId)->max('position') + 1;
        }

        /** @var Reference */
        $workRef = Reference::where('work_id', $workId)
            ->typeWork()
            ->first();

        /** @var Reference */
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
