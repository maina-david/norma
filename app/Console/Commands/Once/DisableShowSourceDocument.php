<?php

namespace App\Console\Commands\Once;

use App\Jobs\Corpus\CacheWorkContent;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class DisableShowSourceDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expressions:disable-show-source-document';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check active expressions with converted documents and disable viewing source document.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Work::active()
            ->whereNotNull('catalogue_work_id')
            ->whereHas('activeExpression', function ($builder) {
                $builder->where('show_source_document', true)->has('convertedDocument');
            })
            ->get()
            ->each(function (Work $work) {
                WorkExpression::whereKey($work->active_work_expression_id)->update(['show_source_document' => false]);
                CacheWorkContent::dispatch($work);
                $this->info("Disabled for work {$work->id}");
            });

        $this->info('Done!');

        return 0;
    }
}
