<?php

namespace App\Actions\Corpus\Reference;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class InsertNewReferenceBelow
{
    use AsAction;

    /**
     * @param Reference $belowReference
     *
     * @return Reference
     */
    public function handle(Reference $belowReference): Reference
    {
        // move all refs after one position up
        Reference::where('work_id', $belowReference->work_id)
            ->where('position', '>', $belowReference->position ?? 0)
            ->update(['position' => DB::raw('position + 1')]);

        /** @var Reference */
        $workRef = Reference::where('work_id', $belowReference->work_id)
            ->typeWork()
            ->first();
        $position = ($belowReference->position ?? 0) + 1;

        /** @var Reference */
        $newRef = Reference::create([
            'parent_id' => $workRef->id,
            'work_id' => $belowReference->work_id,
            'status' => ReferenceStatus::pending()->value,
            'type' => ReferenceType::citation()->value,
            'volume' => 1,
            'level' => 1,
            'start' => $position,
            'position' => $position,
        ]);

        ReferenceContentDraft::create([
            'reference_id' => $newRef->id,
        ]);

        return $newRef;
    }
}
