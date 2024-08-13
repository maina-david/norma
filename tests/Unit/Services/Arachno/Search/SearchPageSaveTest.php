<?php

namespace Tests\Unit\Services\Arachno\Search;

use App\Jobs\Arachno\Search\IndexSearchPage;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\SearchPage;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Geonames\Location;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Search\SearchPageSave;
use App\Stores\Arachno\SearchPageContentStore;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class SearchPageSaveTest extends TestCase
{
    public function testSavePage(): void
    {
        Bus::fake();
        $base = 'https://example.com';
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make(['url' => $base]);
        /** @var ContentCache */
        $cache = ContentCache::factory()->create();
        /** @var Location */
        $location = Location::factory()->create();
        $language = 'fra';

        $html = '<html><body><div></div></body></html>';
        $domCrawler = new Crawler($html);
        $pageCrawl = new PageCrawl($domCrawler, $pageUrl, [], $cache);

        $path = '/test/path';
        $this->partialMock(SearchPageContentStore::class, function (MockInterface $mock) use ($path) {
            $mock->shouldReceive('put')->andReturn($path);
        });

        $searchPage = app(SearchPageSave::class)->savePage($pageCrawl, languageCode: $language);
        $this->assertSame($path, $searchPage->latestVersion?->content_path);
        $this->assertSame(1, $searchPage->versions()->count());

        // content hasn't changed, so when we run again, shouldn't create another version
        $countBefore = SearchPage::count();
        $searchPage = app(SearchPageSave::class)->savePage($pageCrawl);

        $this->assertSame(1, $searchPage->versions()->count());
        $this->assertSame($countBefore, SearchPage::count());
        $this->assertSame($language, $searchPage->searchPageMeta?->language_code);

        DB::table((new SearchPage())->getTable())->delete();
        // with primary location
        $searchPage = app(SearchPageSave::class)->savePage($pageCrawl, primaryLocationId: $location->id, languageCode: $language);
        $this->assertSame($location->id, $searchPage->searchPageMeta?->primary_location_id);
        Bus::assertDispatched(IndexSearchPage::class);
    }
}
