<?php

namespace Tests\Feature\Console\Search;

use App\Jobs\Search\Elastic\SearchFullIndexJob;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\My\MyTestCase;

class SearchFullIndexTest extends MyTestCase
{
    public function testSearchFullIndexCommand(): void
    {
        Queue::fake();
        $this->artisan('search:full-index')->assertExitCode(0);
        Queue::assertPushedOn('long-running', SearchFullIndexJob::class);
    }
}
