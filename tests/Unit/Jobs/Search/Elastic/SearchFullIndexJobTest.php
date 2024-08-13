<?php

namespace Tests\Unit\Jobs\Search\Elastic;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Enums\Corpus\WorkStatus;
use App\Jobs\Search\Elastic\SearchFullIndexJob;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Services\Search\Elastic\DocumentSearchIndexer;
use Mockery\MockInterface;
use Tests\Feature\My\MyTestCase;

class SearchFullIndexJobTest extends MyTestCase
{
    public function testSearchFullIndexJob(): void
    {
        $work = Work::factory()->create(['status' => WorkStatus::active()->value]);
        Reference::factory()
            ->count(5)
            ->has(ReferenceContent::factory(), 'htmlContent')
            ->create([
                'work_id' => $work->id,
                'type' => ReferenceType::citation()->value,
                'status' => ReferenceStatus::active()->value,
            ]);

        Reference::factory()
            ->count(5)
            ->has(ReferenceContent::factory(), 'htmlContent')
            ->create([
                'work_id' => $work->id,
                'type' => ReferenceType::citation()->value,
                'status' => ReferenceStatus::pending()->value,
            ]);

        $this->partialMock(DocumentSearchIndexer::class, function (MockInterface $mock) {
            $mock->shouldReceive('addReferences')
                ->withArgs(fn ($refs) => $refs->count() === 5)
                ->once(5);
        });

        SearchFullIndexJob::dispatch();
    }
}
