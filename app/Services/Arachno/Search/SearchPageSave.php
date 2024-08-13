<?php

namespace App\Services\Arachno\Search;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Jobs\Arachno\Search\IndexSearchPage;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\SearchPage;
use App\Models\Arachno\SearchPageMeta;
use App\Models\Arachno\SearchPageVersion;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\DomQueryRunner;
use App\Stores\Arachno\SearchPageContentStore;

class SearchPageSave
{
    public function __construct(
        protected SearchPageContentStore $searchPageContentStore,
        protected DomQueryRunner $domQueryRunner,
    ) {
    }

    /**
     * @param PageCrawl     $pageCrawl
     * @param DomQuery|null $contentQuery
     * @param int|null      $primaryLocationId
     * @param string|null   $languageCode
     *
     * @return SearchPage
     */
    public function savePage(
        PageCrawl $pageCrawl,
        ?DomQuery $contentQuery = null,
        ?int $primaryLocationId = null,
        ?string $languageCode = null,
    ): SearchPage {
        $url = $pageCrawl->getFinalRedirectedUrl();
        $uid = SearchPage::hashForDB($url);
        /** @var SearchPage|null */
        $searchPage = SearchPage::where('uid', $uid)->first();

        $fullContent = $pageCrawl->contentCache?->response_body ?? '';
        if ($pageCrawl->isPdf()) {
            // @codeCoverageIgnoreStart
            $content = $fullContent;
        // @codeCoverageIgnoreEnd
        } else {
            $contentQuery = $contentQuery ?? new DomQuery(['query' => 'body', 'type' => DomQueryType::CSS]);
            $content = $this->domQueryRunner->runQuery($contentQuery, $pageCrawl->domCrawler)->outerHtml();
        }
        $contentHash = md5($content);
        $headers = $pageCrawl->contentCache->response_headers ?? [];
        if (!$searchPage) {
            $searchPage = SearchPage::create([
                'uid' => $uid,
                'url' => $url,
                'source_id' => $pageCrawl->pageUrl->crawl?->crawler?->source_id,
                'latest_content_hash' => $contentHash,
            ]);
            $searchPageMeta = SearchPageMeta::create([
                'search_page_id' => $searchPage->id,
                'headers' => $headers,
                'primary_location_id' => $primaryLocationId ?? $this->getPrimaryLocation($pageCrawl),
            ]);
        } else {
            // if unchanged, don't create a new version
            if ($contentHash === $searchPage->latest_content_hash) {
                return $searchPage;
            }
        }

        $path = $this->searchPageContentStore->put($fullContent);

        SearchPageVersion::create([
            'search_page_id' => $searchPage->id,
            'crawl_id' => $pageCrawl->pageUrl->crawl_id,
            'content_hash' => $contentHash,
            'content_path' => $path,
        ]);

        $searchPage->latest_content_hash = $contentHash;
        $searchPage->last_crawled_at = now();
        /** @var SearchPageMeta */
        $searchPageMeta = $searchPage->searchPageMeta;
        $searchPageMeta->headers = $headers;
        $searchPageMeta->language_code = $languageCode ?: $searchPageMeta->language_code;
        $searchPageMeta->primary_location_id = $primaryLocationId ?: $searchPageMeta->primary_location_id;

        $searchPage->save();
        $searchPageMeta->save();

        /** @var ContentCache */
        $contentCache = $pageCrawl->contentCache;
        IndexSearchPage::dispatch(
            $searchPage->id,
            $contentCache->id,
            !$contentQuery ? [] : ['query' => $contentQuery['query'], 'type' => $contentQuery['type']->value]
        )->onQueue('enhance-processing');

        return $searchPage;
    }

    protected function getPrimaryLocation(PageCrawl $pageCrawl): ?int
    {
        $locationsCount = $pageCrawl->pageUrl->crawl?->crawler?->source?->locations()->count() ?? 0;

        return $locationsCount === 1 ? $pageCrawl->pageUrl->crawl?->crawler?->source?->locations()->first()?->id : null;
    }
}
