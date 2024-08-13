<?php

namespace App\Jobs\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentExtract;
use App\Services\Tasks\AITaskGenerator;
use App\Traits\UpdatesReferenceContentExtract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateExtracts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use UpdatesReferenceContentExtract;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 10800;

    /**
     * Create a new job instance.
     *
     * @param array<int, int|string> $referenceIds
     */
    public function __construct(protected array $referenceIds)
    {
        $this->onQueue('long-running');
    }

    /**
     * Execute the job.
     *
     * @param AITaskGenerator $generator
     *
     * @return void
     */
    public function handle(AITaskGenerator $generator): void
    {
        ReferenceContentExtract::whereIn('reference_id', $this->referenceIds)->delete();

        Reference::whereIn('id', $this->referenceIds)
            ->has('htmlContent')
            ->has('refRequirement')
            ->chunkById(200, function ($chunk) use ($generator) {
                $chunk->each(function ($reference) use ($generator) {
                    /** @var Reference $reference */
                    $this->updateFromGPT($reference, $generator);
                });
            });
    }
}
