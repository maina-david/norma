<?php

namespace App\Actions\Search;

use App\Jobs\Search\Elastic\SearchIndexWork;
use App\Services\Search\Elastic\DocumentSearch;
use Lorisleiva\Actions\Concerns\AsAction;

class IndexUnindexedWorks
{
    use AsAction;

    public function __construct(protected DocumentSearch $documentSearch)
    {
    }

    public function handle(): void
    {
        $unindexedIds = $this->documentSearch->getUnindexedWorks();
        foreach ($unindexedIds as $id) {
            SearchIndexWork::dispatch($id)->onQueue('arachno');
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function asJob(): void
    {
        $this->handle();
    }
}
