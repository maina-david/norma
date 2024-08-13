<?php

namespace App\Console\Commands\Once;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class SetAssessmentItemResponseFrequency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:set-assess-frequency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy over the existing frequency from the Assessment Items';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        AssessmentItem::whereNotNull('frequency')
            ->select(['id', 'frequency'])
            ->chunk(1000, function ($items) {
                $items->each(function ($item) {
                    AssessmentItemResponse::where('assessment_item_id', $item->id)->update(['frequency' => $item->frequency]);
                    $this->info("Updated SAI {$item->id}");
                });
            });

        return 0;
    }
}
