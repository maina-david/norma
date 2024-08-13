<?php

namespace App\Console\Commands\Once;

use App\Actions\Arachno\Legacy\SyncDoc;
use App\Models\Corpus\Work;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class AugustModelBackfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:august-model-backfill {chunk?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts legacy documents to august model. Meant to be run on an interval e.g. once every minute so we can slowly backfill the data without overwhelming the DB etc..';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $chunk = (int) $this->argument('chunk');
        $chunk = $chunk === 0 ? 60 : $chunk;

        $works = Work::active()
            ->doesntHave('docs')
            ->has('activeExpression')
            ->has('references')
            ->with(['activeExpression'])
            ->limit($chunk)
            ->get();

        foreach ($works as $index => $work) {
            if (app()->environment('local')) {
                $this->info((string) $work->id);
            }
            SyncDoc::dispatch($work->activeExpression);
        }

        return 0;
    }
}
