<?php

namespace App\Console\Commands\Once;

use App\Models\Customer\Libryo;
use App\Stores\Customer\LibryoRequirementsCollectionStore;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class LibryoCollectionsBackfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:libryo-collections-backfill';

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
    public function handle(LibryoRequirementsCollectionStore $libryoRequirementsCollectionStore): int
    {
        Libryo::with(['requirementsCollection.ancestors'])->chunk(100, function ($libryos) use ($libryoRequirementsCollectionStore) {
            $this->line((string) ($libryos[0]->id ?? ''));
            foreach ($libryos as $libryo) {
                $libryoRequirementsCollectionStore->syncCollectionsFromLocationId($libryo);
            }
        });

        return 0;
    }
}
