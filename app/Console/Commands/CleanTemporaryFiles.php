<?php

namespace App\Console\Commands;

use App\Services\TempFileManager;
use Illuminate\Console\Command;

/** @codeCoverageIgnore */
class CleanTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monolith:clean-temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean temporary files.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        app(TempFileManager::class)->clean();

        return 0;
    }
}
