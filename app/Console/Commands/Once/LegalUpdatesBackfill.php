<?php

namespace App\Console\Commands\Once;

use App\Actions\Notify\LegalUpdate\SyncFromWork;
use App\Models\Notify\LegalUpdate;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class LegalUpdatesBackfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:legal-updates-backfill {fromId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates the legal_updates table from work data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $fromId = $this->argument('fromId');
        LegalUpdate::has('work')
            ->when($fromId, fn ($q) => $q->where('id', '>=', $fromId))
            ->with(['work', 'work.workReference.legalDomains'])
            ->chunk(5, function ($updates) {
                foreach ($updates as $update) {
                    $this->info((string) $update->id);
                    SyncFromWork::run($update);
                }
            });

        return 0;
    }
}
