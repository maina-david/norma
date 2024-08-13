<?php

namespace Tests\Unit\Actions\Search;

use App\Actions\Search\IndexUnindexedWorks;
use App\Jobs\Search\Elastic\SearchIndexWork;
use App\Services\Search\Elastic\DocumentSearch;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class IndexUnindexedWorksTest extends TestCase
{
    public function testHandle(): void
    {
        Bus::fake();
        $this->partialMock(DocumentSearch::class, fn ($mock) => $mock->shouldReceive('getUnindexedWorks')->andReturn([1, 2, 3]));
        app(IndexUnindexedWorks::class)->handle();
        Bus::assertDispatched(SearchIndexWork::class);
    }
}
