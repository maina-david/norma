<?php

namespace App\Jobs\Corpus;

use App\Models\Corpus\Reference;
use App\Services\Tasks\AITaskGenerator;
use App\Traits\UpdatesReferenceContentExtract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReferenceContentExtracts implements ShouldQueue
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
     * @param int               $workId
     * @param array<int, mixed> $libryos
     */
    public function __construct(protected int $workId, protected array $libryos = [])
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
        Reference::has('htmlContent')
            ->where(fn ($query) => $query->has('refRequirement')->orHas('requirementDraft'))
            ->where(fn ($query) => $query->has('actionAreas')->orHas('actionAreaDrafts'))
            ->doesntHave('contentExtracts')
            ->where('work_id', $this->workId)
            ->when(!empty($this->libryos), fn ($builder) => $builder->whereHas('libryos', fn ($query) => $query->whereKey($this->libryos)))
            ->chunkById(200, function ($chunk) use ($generator) {
                $chunk->each(function ($reference) use ($generator) {
                    /** @var Reference $reference */
                    $this->updateFromGPT($reference, $generator);
                });
            });
    }
}
