<?php

namespace App\Console\Commands\Corpus;

use App\Jobs\Corpus\GenerateReferenceContentExtracts;
use App\Models\Corpus\Work;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class GenerateExtractsForNorma extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corpus:generate-extracts-for-norma {--normas=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Reference Extracts for the references in the Norma';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var string $normas */
        $normas = $this->option('normas') ?? '';
        $normas = explode(',', $normas);

        Work::whereHas('normas', fn ($query) => $query->whereKey($normas))
            ->select(['id'])
            ->chunk(200, function ($batch) use ($normas) {
                $batch->each(function ($work) use ($normas) {
                    GenerateReferenceContentExtracts::dispatch($work->id, $normas);
                    $this->info("Queued work {$work->id}");
                });
            });

        return 0;
    }
}
