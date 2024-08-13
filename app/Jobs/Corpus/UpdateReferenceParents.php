<?php

namespace App\Jobs\Corpus;

use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class UpdateReferenceParents implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param Work $work
     */
    public function __construct(protected Work $work)
    {
        $this->queue = 'long-running';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var Reference|null $workReference */
        $workReference = Reference::where('work_id', $this->work->id)
            ->where('type', ReferenceType::work()->value)
            ->first();

        /** @var Collection<int, Reference> $reversedReferences */
        $reversedReferences = collect();

        if ($workReference) {
            $reversedReferences->push($workReference);
        }

        Reference::where('work_id', $this->work->id)
            ->where('type', ReferenceType::citation()->value)
            ->select(['id', 'start', 'level', 'parent_id', 'type', 'volume', 'work_id'])
            ->orderBy('volume')
            ->orderBy('start')
            ->chunk(1000, function ($references) use ($workReference, $reversedReferences) {
                /** @var Reference $reference */
                foreach ($references as $reference) {
                    $parent = self::findParent($reference, $reversedReferences, $workReference);

                    if ($parent && $parent->id !== $reference->parent_id) {
                        $reference->update(['parent_id' => $parent->id]);
                    }

                    $reversedReferences->prepend($reference);
                }
            });
    }

    /**
     * Find the parent reference.
     *
     * @param Reference                        $current
     * @param Collection<array-key, Reference> $references
     * @param Reference|null                   $workReference
     *
     * @return Reference|null
     */
    private static function findParent(Reference $current, Collection $references, ?Reference $workReference): ?Reference
    {
        /** @var Reference|null $matched */
        $matched = $references->firstWhere('level', '<', $current->level);

        if ($matched) {
            return $matched;
        }

        // @codeCoverageIgnoreStart
        return $current->findPossibleParent(['id']) ?? $workReference;
        // @codeCoverageIgnoreEnd
    }
}
