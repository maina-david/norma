<?php

namespace App\Services\Arachno\Frontier;

use App\Enums\Arachno\CrawlType;
use App\Jobs\Arachno\CheckCrawlComplete;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\CrawlerConfigFactory;
use App\Services\Arachno\Crawlers\CrawlerConfigInterface;
use App\Services\Arachno\Parse\DocMetaSaver;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CrawlerManager
{
    /**
     * @param UrlFrontierLinkManager $frontierManager
     */
    public function __construct(
        protected UrlFrontierLinkManager $frontierManager,
        protected LinkToUri $linkToUri,
        protected Fetch $fetch,
        protected CrawlerConfigFactory $crawlerConfigFactory,
        protected DocMetaSaver $docMetaSaver,
    ) {
    }

    /**
     * @param Crawler $crawler
     *
     * @return Crawl
     */
    public function createCrawl(Crawler $crawler): Crawl
    {
        /** @var Crawl */
        $crawl = Crawl::create([
            'crawler_id' => $crawler->id,
        ]);

        return $crawl;
    }

    /**
     * @param Crawl $crawl
     *
     * @return void
     */
    public function startCrawl(Crawl $crawl): void
    {
        if (is_null($crawl->crawler)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        $crawl->startNow();
        $config = $this->crawlerConfigFactory->createConfigForCrawler($crawl->crawler);

        if (method_exists($config, 'customStartCrawl')) {
            // @codeCoverageIgnoreStart
            $config->customStartCrawl($crawl);

            return;
            // @codeCoverageIgnoreEnd
        }

        $crawlType = CrawlType::from($crawl->crawl_type);
        foreach ($config->getStartUrls($crawlType) as $frontierLink) {
            $url = $frontierLink->url;
            $uri = $this->linkToUri->convert($url);
            $frontierLink->crawl_id = $crawl->id;
            $frontierLink->crawl_type = $crawl->crawl_type;
            $this->frontierManager->addToFrontier(
                $uri,
                $frontierLink
            );
        }
    }

    /**
     * @param UrlFrontierLink $pageUrl
     *
     * @return DomCrawler
     */
    public function crawlUrl(UrlFrontierLink $pageUrl): DomCrawler
    {
        $domCrawler = new DomCrawler('', $pageUrl->url);
        if (!is_null($pageUrl->crawled_at)) {
            // @codeCoverageIgnoreStart
            return $domCrawler;
            // @codeCoverageIgnoreEnd
        }
        $pageUrl->setCrawledNow();
        if (is_null($pageUrl->crawl) || is_null($pageUrl->crawl->crawler)) {
            // @codeCoverageIgnoreStart
            return $domCrawler;
            // @codeCoverageIgnoreEnd
        }
        $crawlerConfig = $this->crawlerConfigFactory->createConfigForCrawler($pageUrl->crawl->crawler);
        $pageCrawl = new PageCrawl($domCrawler, $pageUrl, $crawlerConfig->getCrawlerSettings());
        $pageCrawl->log('Starting crawl');

        // specific crawler config can implement it's own crawlUrl implementation (e.g. if fetching from FTP...)
        if (method_exists($crawlerConfig, 'crawlUrl')) {
            // @codeCoverageIgnoreStart
            $crawler = $crawlerConfig->crawlUrl($pageCrawl);
            $this->terminateCrawl($pageCrawl);

            return $crawler;
            // @codeCoverageIgnoreEnd
        }

        // always use proxy when running search crawler
        if ($pageUrl->crawl_type === CrawlType::SEARCH->value) {
            // @codeCoverageIgnoreStart
            $pageCrawl->setProxySettings(['provider' => 'scraping_bee']);
            // @codeCoverageIgnoreEnd
        }
        $crawlerConfig->preFetch($pageCrawl);
        $contentCache = $this->fetch->fetchPage($pageCrawl);
        if (is_null($contentCache)) {
            // @codeCoverageIgnoreStart
            $this->terminateCrawl($pageCrawl);

            return $domCrawler;
            // @codeCoverageIgnoreEnd
        }
        $pageUrl->update(['content_cache_id' => $contentCache->id]);
        $pageCrawl->setContentCache($contentCache);

        $this->parseCrawl($crawlerConfig, $pageCrawl, $contentCache);

        $this->terminateCrawl($pageCrawl);

        return $domCrawler;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function terminateCrawl(PageCrawl $pageCrawl): void
    {
        $pageCrawl->persistLog();
    }

    /**
     * @param CrawlerConfigInterface $crawlerConfig
     * @param PageCrawl              $pageCrawl
     * @param ContentCache           $contentCache
     *
     * @return void
     */
    protected function parseCrawl(CrawlerConfigInterface $crawlerConfig, PageCrawl $pageCrawl, ContentCache $contentCache): void
    {
        if ($pageCrawl->isPdf()) {
            // @codeCoverageIgnoreStart
            if (is_null($contentCache->response_body)) {
                $pageCrawl->log('PDF fetch did not return a response body');

                return;
            }
        // @codeCoverageIgnoreEnd
        } elseif ($pageCrawl->isDocx()) { // still to be implemented...
        } else {
            if (!is_null($contentCache->response_body)) {
                $pageCrawl->domCrawler->addContent($contentCache->response_body, $pageCrawl->mimeType);
            }
        }

        if ($pageCrawl->pageUrl->frontierMeta && isset($pageCrawl->pageUrl->frontierMeta->meta['source_unique_id'])) {
            $this->docMetaSaver->setDocMetaProperty($pageCrawl, 'source_unique_id', $pageCrawl->pageUrl->frontierMeta->meta['source_unique_id']);
        }
        $crawlerConfig->parsePage($pageCrawl);

        $doc = $pageCrawl->getDoc();
        if ($doc) {
            /** @var int $crawlId */
            $crawlId = $pageCrawl->pageUrl->crawl_id;

            CheckCrawlComplete::dispatch($doc->id, $crawlId)
                ->delay(now()->addMinutes(config('arachno.check_crawl_complete_delay_minutes')))
                ->onQueue('arachno-checks');
        }

        if ($doc && $doc->isDirty()) {
            $doc->save();
        }
        if ($doc && $doc->docMeta->isDirty()) {
            $doc->docMeta->save();
        }
    }
}
