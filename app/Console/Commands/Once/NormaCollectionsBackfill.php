<?php

namespace App\Console\Commands\Once;

use App\Models\Customer\Norma;
use App\Stores\Customer\NormaRequirementsCollectionStore;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class NormaCollectionsBackfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:norma-collections-backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates the place_collection table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(NormaRequirementsCollectionStore $normaRequirementsCollectionStore): int
    {
        Norma::with(['requirementsCollection.ancestors'])->chunk(100, function ($normas) use ($normaRequirementsCollectionStore) {
            $this->line((string) ($normas[0]->id ?? ''));
            foreach ($normas as $norma) {
                $normaRequirementsCollectionStore->syncCollectionsFromLocationId($norma);
            }
        });

        return 0;
    }
}
