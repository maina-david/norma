<?php

namespace Tests\Feature\Search;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Services\Search\Meilisearch\Highlighter;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeiliSearchEngine;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Mockery\MockInterface;
use Tests\TestCase;

class MeilisearchHighlighterTest extends TestCase
{
    protected $content = '  28    Offence of obstructing or intimidating inspectors   A person must not:  (a)  obstruct, hinder or impede an inspector in the exercise of the inspector’s functions under this Act or the regulations, or  (b)  intimidate or threaten or attempt to intimidate an inspector in the exercise of the inspector’s functions under this Act or the regulations.  Maximum penalty:  (a)  in the case of a corporation—750 penalty units, or  (b)  in the case of an individual—225 penalty units. ';

    public function testHighlightingWorks(): void
    {
        $reference = Reference::withoutEvents(fn () => Reference::factory()->create());
        $childReference = Reference::withoutEvents(fn () => Reference::factory()->create());

        $reference->work->children()->attach($childReference->work_id);

        $this->app->offsetUnset(EngineManager::class);

        $index = $this->mock(Indexes::class, function (MockInterface $mock) use ($reference) {
            $mock->shouldReceive('search')->andReturn([
                'hits' => [
                    [
                        'id' => $reference->id,
                        'cached_content' => $this->content,
                        '_formatted' => [
                            'cached_content' => '...28    Offence of obstructing or <em class="search-result-highlighted">intimidat</em>ing inspectors   A person must not...',
                        ],
                    ],
                ],
            ]);
        });

        $search = $this->mock(Client::class, function (MockInterface $mock) use ($index) {
            $mock->shouldReceive('index')->andReturn($index);
        });

        $this->partialMock(EngineManager::class, function (MockInterface $mock) use ($search) {
            $mock->shouldReceive('createMeilisearchDriver')->andReturn(new MeiliSearchEngine($search, false));
        });

        $works = Work::where('id', $reference->work_id)->with('children')->paginate(10);

        $results = app(Highlighter::class)->highlightForWorks($works, 'person');

        $highlighted = $results->first()->references->first();

        $this->assertSame('...28    Offence of obstructing or <em class="search-result-highlighted">intimidat</em>ing inspectors   A person must not...', $highlighted->_searchHighlights[0]);
    }
}
