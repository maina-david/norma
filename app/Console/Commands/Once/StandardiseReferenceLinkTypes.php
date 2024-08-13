<?php

namespace App\Console\Commands\Once;

use App\Enums\Corpus\ReferenceLinkType;
use App\Models\Corpus\Pivots\ReferenceReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
 */
class StandardiseReferenceLinkTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:standardise-reference-link-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the link types used to only have 3.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $mapping = [
            2 => ReferenceLinkType::READ_WITH->value,
        ];

        $table = (new ReferenceReference())->getTable();

        foreach ($mapping as $old => $new) {
            DB::statement("update ignore {$table} set link_type = {$new} where link_type = {$old}");
            ReferenceReference::where('link_type', $old)->delete();
        }

        return 0;
    }
}
