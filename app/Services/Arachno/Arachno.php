<?php

namespace App\Services\Arachno;

use App\Actions\Arachno\AddCatalogueDocsToFrontier;
use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Jobs\Arachno\OcrPdf;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\SearchPage;
use App\Models\Arachno\SourceCategory;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Arachno\UrlFrontierMeta;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\DocMetaSaver;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\DomQueryRunner;
use App\Services\Arachno\Parse\LinkExtractor;
use App\Services\Arachno\Parse\PageCapture;
use App\Services\Arachno\Parse\PdfCapture;
use App\Services\Arachno\Parse\TocCapture;
use App\Services\Arachno\Parse\TocItemDraft;
use App\Services\Arachno\Search\SearchPageSave;
use App\Stores\Corpus\CatalogueDocStore;
use DOMNode;
use Generator;
use GuzzleHttp\Cookie\CookieJarInterface;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Arachno
{
    public function __construct(
        protected DomQueryRunner $domQueryRunner,
        protected CatalogueDocStore $catalogueDocStore,
        protected LinkExtractor $linkExtractor,
        protected UrlFrontierLinkManager $urlFrontierLinkManager,
        protected PageCapture $pageCapture,
        protected DocMetaSaver $docMetaSaver,
        protected TocCapture $tocCapture,
        protected PdfCapture $pdfCapture,
        protected Fetch $fetch,
        protected AddCatalogueDocsToFrontier $addCatalogueDocsToFrontier,
        protected SearchPageSave $searchPageSave,
    ) {
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl $pageCrawl
     * @param DomQuery  $query
     *
     * @return bool
     */
    public function matchQuery(PageCrawl $pageCrawl, DomQuery $query): bool
    {
        return $this->domQueryRunner->queryMatches($query, $pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $query
     *
     * @return bool
     */
    public function matchUrl(PageCrawl $pageCrawl, string $query): bool
    {
        $q = new DomQuery([
            'type' => DomQueryType::REGEX,
            'query' => $query,
            'source' => DomQuerySource::URL,
        ]);

        return $this->domQueryRunner->queryMatches($q, $pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $query
     *
     * @return bool
     */
    public function matchCSS(PageCrawl $pageCrawl, string $query): bool
    {
        $q = new DomQuery([
            'type' => DomQueryType::CSS,
            'query' => $query,
        ]);

        return $this->domQueryRunner->queryMatches($q, $pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $query
     *
     * @return bool
     */
    public function matchXpath(PageCrawl $pageCrawl, string $query): bool
    {
        $q = new DomQuery([
            'type' => DomQueryType::XPATH,
            'query' => $query,
        ]);

        return $this->domQueryRunner->queryMatches($q, $pageCrawl);
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    public function followLink(
        PageCrawl $pageCrawl,
        UrlFrontierLink $link,
        bool $followNewLinksOnly = false
    ): ?UrlFrontierLink {
        return $this->linkExtractor->addLinkToFrontier(
            $link,
            $pageCrawl,
            $followNewLinksOnly
        );
    }

    public function followLinks(
        PageCrawl $pageCrawl,
        DomQuery $query,
        bool $followNewLinksOnly = false,
    ): void {
        $this->linkExtractor->extractLinksForQuery($query, $pageCrawl, $followNewLinksOnly);
    }

    public function followLinksCSS(
        PageCrawl $pageCrawl,
        string $query,
        bool $followNewLinksOnly = false,
    ): void {
        $q = new DomQuery([
            'type' => DomQueryType::CSS,
            'query' => $query,
        ]);

        $this->linkExtractor->extractLinksForQuery($q, $pageCrawl, $followNewLinksOnly);
    }

    public function followLinksXpath(
        PageCrawl $pageCrawl,
        string $query,
        bool $followNewLinksOnly = false,
    ): void {
        $q = new DomQuery([
            'type' => DomQueryType::XPATH,
            'query' => $query,
        ]);

        $this->linkExtractor->extractLinksForQuery($q, $pageCrawl, $followNewLinksOnly);
    }

    /**
     * @param UrlFrontierLink $link
     * @param DocMetaDto      $docMetaDto
     *
     * @return UrlFrontierMeta
     */
    public function addMetaToLink(UrlFrontierLink $link, DocMetaDto $docMetaDto): UrlFrontierMeta
    {
        return $this->urlFrontierLinkManager->addMetaToLink($link, $docMetaDto);
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl       $pageCrawl
     * @param array<DomQuery> $bodyQueries
     * @param array<DomQuery> $headQueries
     * @param array<DomQuery> $removeQueries
     * @param callable|null   $postProcessCssCallable Callback that is called after processing CSS that you can use to alter the CSS before it's stored
     * @param callable|null   $preStoreCallable       Callback that is passed the DomCrawler object to allow you to update the DOM before it's stored
     *
     * @return DomCrawler|null
     */
    public function capture(
        PageCrawl $pageCrawl,
        array $bodyQueries = [],
        array $headQueries = [],
        array $removeQueries = [],
        ?callable $postProcessCssCallable = null,
        ?callable $preStoreCallable = null,
    ): ?DomCrawler {
        return $this->pageCapture->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
            $postProcessCssCallable,
            $preStoreCallable,
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $content
     *
     * @return void
     */
    public function captureContent(
        PageCrawl $pageCrawl,
        string $content,
    ): void {
        $this->pageCapture->store(
            $pageCrawl,
            $content,
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function capturePDF(PageCrawl $pageCrawl): void
    {
        $pageCrawl->log('Capturing PDF Page: ' . $pageCrawl->getFinalRedirectedUrl());
        $this->saveDoc($pageCrawl);

        if (is_null($pageCrawl->contentCache)) {
            // @codeCoverageIgnoreStart
            $pageCrawl->log('Pdf content is missing in cache. Ensure you\'ve downloaded a PDF document before attempting to capture');
            throw new RuntimeException('No PDF content found in cache when attempting capture. Ensure PDF is dowloaded to capture.');
            // @codeCoverageIgnoreEnd
        }

        if (!$pageCrawl->getDoc()) {
            // @codeCoverageIgnoreStart
            $pageCrawl->log('A doc needs to be created first before saving the pdf. Make sure you set some meta data first');
            throw new RuntimeException('A doc needs to be created first before saving the pdf. Make sure you set some meta data first');
            // @codeCoverageIgnoreEnd
        }

        if ($pageCrawl->needsOcr()) {
            $options = $pageCrawl->getOcrSettings();

            OcrPdf::dispatch(
                $pageCrawl->getDoc()->id,
                $pageCrawl->contentCache->id,
                $options
            );
        } else {
            $this->pdfCapture->capture(
                $pageCrawl->getDoc(),
                $pageCrawl->contentCache->response_body ?? ''
            );
        }
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl    $pageCrawl
     * @param CatalogueDoc $catalogueDoc
     * @param Crawler|null $crawler      If a different crawler should be used for fetching docs, specify that crawler here. Otherwise the same crawler will be used by default.
     *
     * @return CatalogueDoc
     */
    public function createCatalogueDoc(
        PageCrawl $pageCrawl,
        CatalogueDoc $catalogueDoc,
        ?Crawler $crawler = null,
    ): CatalogueDoc {
        /** @var Crawler $crawler */
        $crawler = $crawler ?: $pageCrawl->pageUrl->crawl?->crawler;

        return $this->catalogueDocStore->create($catalogueDoc, $crawler);
    }

    /**
     * @param CatalogueDoc      $catalogueDoc
     * @param array<int|string> $categories
     *
     * @return void
     */
    public function addCategoriesToCatalogueDoc(
        CatalogueDoc $catalogueDoc,
        array $categories,
    ): void {
        if (empty($categories)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        /** @var Collection<SourceCategory> */
        $sourceCategories = is_numeric($categories[0]) ? SourceCategory::whereKey($categories)->get() : SourceCategory::where('title', $categories)->get();
        $this->catalogueDocStore->attachSourceCategories($catalogueDoc, $sourceCategories);
    }

    /**
     * Adds docs from the catalogue to the Frontier for fetching, where these docs have works
     * associated with them. ie. we'll only fetch docs that are used by us. Use this within a
     * WORK_CHANGES_DISCOVERY crawl, where you know that certain works have been updated. e.g. some
     * sources have a "What's new" page or feed...
     *
     * @param array<string> $sourceUniqueIds
     * @param int           $sourceId
     *
     * @return void
     */
    public function fetchChangedDocs(
        array $sourceUniqueIds,
        int $sourceId,
    ): void {
        $uids = [];
        foreach ($sourceUniqueIds as $id) {
            $uids[] = (new CatalogueDoc([
                'source_unique_id' => $id,
                'source_id' => $sourceId,
            ]))->setUid()->uid;
        }
        $ids = CatalogueDoc::whereIn('uid', $uids)->pluck('id')->all();
        $this->addCatalogueDocsToFrontier->handle($ids, true);
    }

    /**
     * @param Collection<CatalogueDoc> $docs
     * @param int                      $sourceId
     *
     * @return void
     */
    public function fetchChangedUpdatedDocs(
        Collection $docs,
        int $sourceId,
    ): void {
        $uids = [];
        foreach ($docs as $doc) {
            $doc->source_id = $sourceId;
            $doc->setUid();
            $uids[] = $doc->uid;
        }
        /** @var Collection<CatalogueDoc> */
        $docs = $docs->keyBy('uid');

        $ids = [];
        CatalogueDoc::whereIn('uid', $uids)
            ->chunk(100, function ($catalogueDocs) use ($docs, &$ids) {
                foreach ($catalogueDocs as $catDoc) {
                    /** @var CatalogueDoc|null $doc */
                    $doc = $docs[$catDoc->uid] ?? null;
                    if ($doc && $doc->last_updated_at && !$doc->last_updated_at->isSameDay($catDoc->last_updated_at ?? now())) {
                        $ids[] = $catDoc->id;
                        $catDoc->update(['last_updated_at' => $doc->last_updated_at]);
                    }
                }
            });
        $this->addCatalogueDocsToFrontier->handle($ids, true);
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc|null
     */
    public function saveDoc(PageCrawl $pageCrawl): ?Doc
    {
        $doc = $pageCrawl->getDoc();
        if ($doc && $doc->isDirty()) {
            $doc->save();
        }
        if ($doc && $doc->docMeta->isDirty()) {
            $doc->docMeta->save();
        }

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $property
     * @param mixed     $value
     *
     * @return Doc
     */
    public function setDocMetaProperty(PageCrawl $pageCrawl, string $property, $value): Doc
    {
        return $this->docMetaSaver->setDocMetaProperty($pageCrawl, $property, $value);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $property
     * @param string    $query
     *
     * @return Doc
     */
    public function setDocMetaPropertyByCSS(PageCrawl $pageCrawl, string $property, string $query): Doc
    {
        $q = new DomQuery([
            'type' => DomQueryType::CSS,
            'query' => $query,
        ]);
        $value = $this->domQueryRunner->getTextByQuery($q, $pageCrawl);

        return $this->setDocMetaProperty($pageCrawl, $property, $value);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $property
     * @param string    $query
     *
     * @return Doc
     */
    public function setDocMetaPropertyByXpath(PageCrawl $pageCrawl, string $property, string $query): Doc
    {
        $q = new DomQuery([
            'type' => DomQueryType::XPATH,
            'query' => $query,
        ]);
        $value = $this->domQueryRunner->getTextByQuery($q, $pageCrawl);

        return $this->setDocMetaProperty($pageCrawl, $property, $value);
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $query
     * @param callable  $callback
     *
     * @return void
     */
    public function each(PageCrawl $pageCrawl, string $query, callable $callback): void
    {
        foreach ($pageCrawl->domCrawler->filter($query) as $node) {
            /** @var DOMNode $node */
            call_user_func($callback, $node);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $query
     * @param callable  $callback
     *
     * @return void
     */
    public function eachX(PageCrawl $pageCrawl, string $query, callable $callback): void
    {
        foreach ($pageCrawl->domCrawler->filterXPath($query) as $node) {
            /** @var DOMNode $node */
            call_user_func($callback, $node);
        }
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl          $pageCrawl
     * @param string             $tocQuery
     * @param callable|null|null $criteriaFunc
     * @param callable|null|null $extractLabelFunc
     * @param callable|null|null $extractLinkFunc
     * @param callable|null|null $extractUniqueIdFunc
     * @param callable|null|null $determineDepthFunc
     *
     * @return void
     */
    public function captureTocCSS(
        PageCrawl $pageCrawl,
        string $tocQuery,
        ?callable $criteriaFunc = null,
        ?callable $extractLabelFunc = null,
        ?callable $extractLinkFunc = null,
        ?callable $extractUniqueIdFunc = null,
        ?callable $determineDepthFunc = null,
    ): void {
        $q = new DomQuery([
            'type' => DomQueryType::CSS,
            'query' => $tocQuery,
        ]);
        $this->saveDoc($pageCrawl);

        $this->tocCapture->capture(
            $pageCrawl,
            $q,
            $criteriaFunc,
            $extractLabelFunc,
            $extractLinkFunc,
            $extractUniqueIdFunc,
            $determineDepthFunc,
        );
    }

    /**
     * @param PageCrawl          $pageCrawl
     * @param string             $tocQuery
     * @param callable|null|null $criteriaFunc
     * @param callable|null|null $extractLabelFunc
     * @param callable|null|null $extractLinkFunc
     * @param callable|null|null $extractUniqueIdFunc
     * @param callable|null|null $determineDepthFunc
     *
     * @return void
     */
    public function captureTocXpath(
        PageCrawl $pageCrawl,
        string $tocQuery,
        ?callable $criteriaFunc = null,
        ?callable $extractLabelFunc = null,
        ?callable $extractLinkFunc = null,
        ?callable $extractUniqueIdFunc = null,
        ?callable $determineDepthFunc = null,
    ): void {
        $q = new DomQuery([
            'type' => DomQueryType::XPATH,
            'query' => $tocQuery,
        ]);

        $this->saveDoc($pageCrawl);

        $this->tocCapture->capture(
            $pageCrawl,
            $q,
            $criteriaFunc,
            $extractLabelFunc,
            $extractLinkFunc,
            $extractUniqueIdFunc,
            $determineDepthFunc,
        );
    }

    /**
     * @param PageCrawl               $pageCrawl
     * @param Generator<TocItemDraft> $itemsGenerator
     *
     * @return void
     */
    public function captureTocFromGenerator(PageCrawl $pageCrawl, Generator $itemsGenerator): void
    {
        $this->saveDoc($pageCrawl);
        $this->tocCapture->createItemsAndAddtoFrontier($itemsGenerator, $pageCrawl);
    }

    /**
     * @param PageCrawl           $pageCrawl
     * @param array<TocItemDraft> $drafts
     *
     * @return void
     */
    public function captureTocFromDraftArray(PageCrawl $pageCrawl, array $drafts): void
    {
        $generator = $this->tocItemArrayToGenerator($drafts);
        $this->captureTocFromGenerator($pageCrawl, $generator);
    }

    /**
     * @param array<TocItemDraft> $items
     *
     * @return Generator<TocItemDraft>
     */
    public function tocItemArrayToGenerator(array $items): Generator
    {
        foreach ($items as $item) {
            yield $item;
        }
    }

    /**
     * @param PageCrawl    $pageCrawl
     * @param TocItemDraft $item
     *
     * @return TocItem
     */
    public function createTocItem(PageCrawl $pageCrawl, TocItemDraft $item): TocItem
    {
        $doc = $pageCrawl->getDoc();
        if (!$doc) {
            throw new RuntimeException('No doc created yet - cannot create TOC items');
        }

        return $this->tocCapture->createItem($item, $pageCrawl, $doc);
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * Fetches the given link from cache (if already fetched this crawl) or from the
     * source and returns a DOM Crawler object if HTML/XML, or an array if JSON.
     *
     * @param PageCrawl           $pageCrawl
     * @param string              $uri
     * @param array<string,mixed> $requestOptions
     *
     * @return DomCrawler|array<mixed>|null
     */
    public function fetchLink(PageCrawl $pageCrawl, string $uri, array $requestOptions = []): DomCrawler|array|null
    {
        $contentCache = $this->fetch->fetchFromCacheOrUrl($pageCrawl, $uri, $requestOptions);
        if (is_null($contentCache) || is_null($contentCache->response_body)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $contentCache->getBody();
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return bool
     */
    public function crawlIsFullCatalogue(PageCrawl $pageCrawl): bool
    {
        return $pageCrawl->pageUrl->crawl?->isTypeFullCatalogue() ?? false;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return bool
     */
    public function crawlIsNewCatalogueWorkDiscovery(PageCrawl $pageCrawl): bool
    {
        return $pageCrawl->pageUrl->crawl?->isTypeNewCatalogueWorkDiscovery() ?? false;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return bool
     */
    public function crawlIsFetchWorks(PageCrawl $pageCrawl): bool
    {
        return $pageCrawl->pageUrl->crawl?->isTypeFetchWorks() ?? false;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return bool
     */
    public function crawlIsWorkChangesDiscovery(PageCrawl $pageCrawl): bool
    {
        return $pageCrawl->pageUrl->crawl?->isTypeWorkChangesDiscovery() ?? false;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return bool
     */
    public function crawlIsForUpdates(PageCrawl $pageCrawl): bool
    {
        return $pageCrawl->pageUrl->crawl?->isTypeForUpdates() ?? false;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return bool
     */
    public function crawlIsSearch(PageCrawl $pageCrawl): bool
    {
        return $pageCrawl->pageUrl->crawl?->isTypeSearch() ?? false;
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------

    /**
     * @param PageCrawl     $pageCrawl
     * @param DomQuery|null $contentQuery
     * @param int|null      $primaryLocationId
     * @param string|null   $languageCode
     *
     * @return SearchPage
     */
    public function indexForSearch(
        PageCrawl $pageCrawl,
        ?DomQuery $contentQuery = null,
        ?int $primaryLocationId = null,
        ?string $languageCode = null
    ): SearchPage {
        return $this->searchPageSave->savePage(
            $pageCrawl,
            $contentQuery,
            $primaryLocationId,
            $languageCode
        );
    }

    /**
     * @param PageCrawl          $pageCrawl
     * @param CookieJarInterface $jar
     *
     * @return void
     */
    public function setCrawlCookies(PageCrawl $pageCrawl, CookieJarInterface $jar): void
    {
        $pageCrawl->pageUrl->crawl?->update(['cookies' => $jar->toArray()]);
    }
}
