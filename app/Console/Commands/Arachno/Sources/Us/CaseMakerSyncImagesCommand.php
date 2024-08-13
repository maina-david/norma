<?php

namespace App\Console\Commands\Arachno\Sources\Us;

use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerImagesSync;
use Exception;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class CaseMakerSyncImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arachno:us-casemaker-images {dir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync\'s casemaker images, provide the root level dir to start with as the argument';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CaseMakerImagesSync $caseMakerImagesSync): int
    {
        /** @var string|null */
        $dir = $this->argument('dir');
        if (!$dir) {
            throw new Exception('Please provide an argument');
        }

        $caseMakerImagesSync->sync($dir);

        return 0;
    }
}
