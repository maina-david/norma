<?php

namespace App\Console\Commands\Corpus;

use App\Jobs\Corpus\GenerateReferenceContentExtracts;
use App\Models\Corpus\Work;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class GenerateExtractsForLibryo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corpus:generate-extracts-for-libryo {--libryos=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Reference Extracts for the references in the Libryo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var string $libryos */
        $libryos = $this->option('libryos') ?? '';
        $libryos = explode(',', $libryos);

        Work::whereHas('libryos', fn ($query) => $query->whereKey($libryos))
            ->select(['id'])
            ->chunk(200, function ($batch) use ($libryos) {
                $batch->each(function ($work) use ($libryos) {
                    GenerateReferenceContentExtracts::dispatch($work->id, $libryos);
                    $this->info("Queued work {$work->id}");
                });
            });

        return 0;
    }
}
