<?php

namespace App\Jobs\Search\Elastic;

use App\Services\Search\Elastic\DocumentSearchIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SearchFullIndexJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param DocumentSearchIndexer $indexer
     *
     * @return void
     */
    public function handle(DocumentSearchIndexer $indexer)
    {
        $indexer->fullIndex();
    }
}
