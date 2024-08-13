<?php

namespace App\Actions\Arachno;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use GuzzleHttp\Psr7\Uri;
use Lorisleiva\Actions\Concerns\AsAction;

class AddCatalogueDocsToFrontier
{
    use AsAction;

    /**
     * Only create a new crawl for each crawler - this maps the crawlers to crawls.
     *
     * @var array<Crawl>
     */
    protected array $crawlersMap;

    public function __construct(
        protected UrlFrontierLinkManager $urlFrontierLinkManager,
    ) {
    }

    /**
     * @param array<int> $docIds
     * @param bool       $onlyWhenHasWork
     *
     * @return void
     */
    public function handle(array $docIds, bool $onlyWhenHasWork = false): void
    {
        CatalogueDoc::whereKey($docIds)
            ->has('crawler')
            ->with(['crawler'])
            ->when($onlyWhenHasWork, fn ($q) => $q->has('work'))
            ->chunk(50, function ($catalogueDocs) {
                foreach ($catalogueDocs as $catalogueDoc) {
                    if (!isset($this->crawlersMap[$catalogueDoc->crawler_id])) {
                        $crawl = $catalogueDoc->crawler?->createCrawl(CrawlType::FETCH_WORKS);
                        if ($crawl) {
                            $crawl->startNow();
                            $this->crawlersMap[$catalogueDoc->crawler_id] = $crawl;
                        }
                    }
                    $this->addToFrontier($catalogueDoc, $this->crawlersMap[$catalogueDoc->crawler_id]);
                }
            });
        CatalogueDoc::whereKey($docIds)
            ->when($onlyWhenHasWork, fn ($q) => $q->has('work'))
            ->update(['fetch_started_at' => now()]);
    }

    protected function addToFrontier(CatalogueDoc $catalogueDoc, Crawl $crawl): ?UrlFrontierLink
    {
        if (!$catalogueDoc->start_url) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        $urlLink = new UrlFrontierLink([
            'url' => $catalogueDoc->start_url,
            'crawl_id' => $crawl->id,
            'crawl_type' => $crawl->crawl_type,
            'needs_browser' => $catalogueDoc->crawler?->needsBrowser() ?? false,
            'catalogue_doc_id' => $catalogueDoc->id,
        ]);
        $uri = new Uri($catalogueDoc->start_url);

        return $this->urlFrontierLinkManager->addToFrontier($uri, $urlLink);
    }
}
