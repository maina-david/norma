<?php

namespace App\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\Link;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\TocItem;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Stores\Arachno\LinkStore;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Throwable;

class PageCapture
{
    public function __construct(
        protected DomQueryRunner $domQueryRunner,
        protected ContentResourceStore $contentResourceStore,
        protected CssCapture $cssCapture,
        protected ImageCapture $imageCapture,
        protected LinkStore $linkStore,
        protected LinkReplacer $linkReplacer
    ) {
    }

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
        $pageCrawl->log('Capturing content');
        $newDomCrawler = $this->captureContent(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries
        );

        $newPageCrawl = new PageCrawl($newDomCrawler, $pageCrawl->pageUrl, $pageCrawl->crawlerSettings);

        $this->cssCapture->capture($newPageCrawl, $postProcessCssCallable);
        $this->imageCapture->capture($newPageCrawl);
        $withHashes = $pageCrawl->crawlerSettings['replace_links_with_hashes'] ?? false;
        $this->linkReplacer->capture($newPageCrawl, $withHashes);

        // if there's no content, don't store it
        if (count($newDomCrawler->filter('body')->children()) === 0) {
            // @codeCoverageIgnoreStart
            return $newDomCrawler;
            // @codeCoverageIgnoreEnd
        }

        if (!is_null($preStoreCallable)) {
            $newDomCrawler = call_user_func($preStoreCallable, $newDomCrawler);
        }

        $html = $newDomCrawler->outerHtml();
        $this->store($pageCrawl, $html);

        return $newDomCrawler;
    }

    /**
     * @param PageCrawl       $pageCrawl
     * @param array<DomQuery> $bodyQueries
     * @param array<DomQuery> $headQueries
     * @param array<DomQuery> $removeQueries
     *
     * @return DomCrawler
     */
    protected function captureContent(
        PageCrawl $pageCrawl,
        array $bodyQueries,
        array $headQueries,
        array $removeQueries,
    ): DomCrawler {
        $newDomCrawler = new DomCrawler('<html><head></head><body></body></html>');
        $head = $newDomCrawler->filter('head');
        $body = $newDomCrawler->filter('body');

        $this->fetchNodesAndAppend($headQueries, $pageCrawl, $head);
        $this->fetchNodesAndAppend($bodyQueries, $pageCrawl, $body);

        $this->removeContent($removeQueries, $newDomCrawler);
        $this->replaceInvalidCharsInIds($newDomCrawler);

        return $newDomCrawler;
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $html
     *
     * @return ContentResource
     */
    public function store(PageCrawl $pageCrawl, string $html): ContentResource
    {
        $redirectedUrl = $pageCrawl->getFinalRedirectedUrl();
        $link = $this->getOrCreateLinkForUrl($redirectedUrl);
        $originalLink = null;
        if ($redirectedUrl !== $pageCrawl->pageUrl->url) {
            $originalLink = $this->getOrCreateLinkForUrl($pageCrawl->pageUrl->url);
        }

        $resource = $this->contentResourceStore->storeResource($html, 'text/html');
        $this->linkStore->attachRelations($link, 'contentResources', collect([$resource]));
        TocItem::where('crawl_id', $pageCrawl->pageUrl->crawl_id)
            ->where(function ($q) use ($link, $originalLink) {
                $q->where('link_id', $link->id);
                $q->when(!is_null($originalLink), function ($q) use ($originalLink) {
                    /** @var Link $originalLink */
                    $q->orWhere('link_id', $originalLink->id);
                });
            })
            ->update(['content_resource_id' => $resource->id]);

        $doc = $pageCrawl->getDoc();
        if ($doc && is_null($doc->first_content_resource_id)) {
            $doc->first_content_resource_id = $resource->id;
            $doc->save();
        }

        return $resource;
    }

    /**
     * @param string $url
     *
     * @return Link
     */
    protected function getOrCreateLinkForUrl(string $url): Link
    {
        $uid = Link::hashForDB($url);
        /** @var Link|null $link */
        $link = Link::where('uid', $uid)->first();
        if (!$link) {
            $link = new Link([
                'url' => $url,
            ]);
            $link->setUid();
            $link->save();
        }

        return $link;
    }

    /**
     * @param array<DomQuery> $queries
     * @param DomCrawler      $crawler
     *
     * @return DomCrawler
     */
    public function removeContent(
        array $queries,
        DomCrawler $crawler,
    ): DomCrawler {
        // remove all scripts
        $queries[] = new DomQuery([
            'type' => DomQueryType::CSS,
            'query' => 'script',
        ]);
        foreach ($queries as $query) {
            $foundElements = $this->domQueryRunner->runQuery($query, $crawler);
            foreach ($foundElements as $node) {
                try {
                    $node->parentNode?->removeChild($node);
                    // @codeCoverageIgnoreStart
                } catch (Throwable $th) {
                }
                // @codeCoverageIgnoreEnd
            }
        }

        return $crawler;
    }

    /**
     * @param array<DomQuery> $queries
     * @param PageCrawl       $pageCrawl
     * @param DomCrawler      $appendToNode
     *
     * @return void
     */
    private function fetchNodesAndAppend(
        array $queries,
        PageCrawl $pageCrawl,
        DomCrawler $appendToNode
    ): void {
        foreach ($queries as $query) {
            if ($query->isTypeFunc()) {
                // if the query is a function, call the function and get the results
                // the function can return a string, or a Symfony Crawler object
                $crawler = call_user_func($query['query'], $pageCrawl);
                if (is_string($crawler)) {
                    // this creates a doc and adds to the body
                    $crawler = new DomCrawler($crawler);
                    $crawler = $crawler->filter('body');
                    if (count($crawler) > 0) {
                        $crawler = $crawler->children();
                    }
                }
            } else {
                $domCrawler = $pageCrawl->domCrawler;
                $crawler = $this->domQueryRunner->runQuery($query, $domCrawler);
            }

            foreach ($crawler as $node) {
                $n = $appendToNode->getNode(0);
                if (is_null($n)) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                $cloned = $n->parentNode?->ownerDocument?->importNode($node, true);
                if (!$cloned) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                /** @var DOMNode $n */
                $n->appendChild($cloned);
            }
        }
    }

    /**
     * @param DomCrawler $newDomCrawler
     *
     * @return void
     *
     * @see TocCapture::replaceInvalidCharsInFragment
     */
    protected function replaceInvalidCharsInIds(DomCrawler $newDomCrawler): void
    {
        foreach ($newDomCrawler->filter('[id]') as $nodeWithId) {
            /** @var DOMElement $nodeWithId */
            $id = $nodeWithId->getAttribute('id');
            // can't contain fullstop
            $id = str_replace('.', '_', $id);
            // if id starts with digit, it's invalid
            if (Str::of($id)->test('/^[0-9]+/')) {
                $id = 'l' . $id;
            }
            $nodeWithId->setAttribute('id', $id);
        }
    }
}
