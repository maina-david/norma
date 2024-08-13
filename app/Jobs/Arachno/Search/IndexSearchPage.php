<?php

namespace App\Jobs\Arachno\Search;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Search\SearchPageIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * @codeCoverageIgnore
 * Just a job that passes all the logic onto the Indexer, so no need to test
 */
class IndexSearchPage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * @param int                 $searchPageId
     * @param int                 $contentCacheId
     * @param array<string,mixed> $contentQuery
     */
    public function __construct(
        protected int $searchPageId,
        protected int $contentCacheId,
        protected array $contentQuery = [])
    {
    }

    /**
     * @param SearchPageIndexer $indexer
     *
     * @return void
     */
    public function handle(SearchPageIndexer $indexer): void
    {
        $contentQuery = empty($this->contentQuery) ? null : new DomQuery(['query' => $this->contentQuery['query'], 'type' => DomQueryType::from($this->contentQuery['type'])]);

        $indexer->indexSearchPage($this->searchPageId, $this->contentCacheId, $contentQuery);
    }
}
