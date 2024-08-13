<?php

namespace App\Console\Commands\Compilation;

use App\Models\Compilation\RequirementsCollection;
use App\Services\Compilation\GeonamesImporter;
use Exception;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class GeonamesAddAsChildren extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:add-as-children {parentLocationId} {--geonameId=*} {--locationTypeId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new locations from given geoname IDs and adds as children to existing parent location';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(GeonamesImporter $geonames)
    {
        /** @var string|null */
        $arg = $this->argument('parentLocationId');
        if (is_null($arg)) {
            $this->error('Please provide a parent location ID as argument');
            exit;
        }
        /** @var RequirementsCollection */
        $reqCollection = RequirementsCollection::findOrFail($arg);

        /** @var array<string> */
        $geonameIds = $this->option('geonameId');

        if (empty($geonameIds)) {
            throw new Exception('At least one geoname ID required');
        }

        $data = [];
        if ($locationTypeId = $this->option('locationTypeId')) {
            $data = ['location_type_id' => (int) $locationTypeId];
        }

        foreach ($geonameIds as $geonameId) {
            $geonames->createAsChildOfExisting(
                (int) $geonameId,
                $reqCollection,
                $data
            );
        }
    }
}