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

class UpdateCitationPositions implements ShouldQueue
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
        $chunk = 300;
        $countChunks = 0;

        Reference::where('work_id', $this->work->id)
            ->where('type', ReferenceType::citation()->value)
            ->select(['id', 'referenceable_id', 'referenceable_type', 'work_id'])
            ->orderBy('volume')
            ->orderBy('start')
            ->with(['citation:id,position'])
            ->chunk($chunk, function ($references) use ($chunk, &$countChunks) {
                foreach ($references as $index => $ref) {
                    if (!$ref->citation) {
                        // @codeCoverageIgnoreStart
                        continue;
                        // @codeCoverageIgnoreEnd
                    }
                    $ref->citation->position = $index + 1 + ($chunk * $countChunks);
                    $ref->citation->save();
                    $ref->update(['position' => $ref->citation->position]);
                }

                $countChunks++;
            });
    }
}
