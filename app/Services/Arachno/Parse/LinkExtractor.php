<?php

namespace App\Services\Arachno\Parse;

use App\Models\Arachno\Link;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use DOMElement;

class LinkExtractor
{
    public function __construct(
        protected UrlFrontierLinkManager $frontierManager,
        protected DomQueryRunner $domQueryRunner,
        protected StringTransformer $stringTransformer,
        protected LinkToUri $linkToUri,
    ) {
    }

    // /**
    //  * @param array<int,array<string, DomQuery|array<DomQuery>>> $queries
    //  * @param PageCrawl                                          $pageCrawl
    //  * @param bool                                               $followNewLinksOnly
    //  *
    //  * @return void
    //  */
    // public function extractLinksForQueries(
    //     array $queries,
    //     PageCrawl $pageCrawl,
    //     bool $followNewLinksOnly = false,
    // ): void {
    //     foreach ($queries as $page) {
    //         /** @var DomQuery */
    //         $matchQuery = $page['should_run_query'];
    //         if (!$this->domQueryRunner->queryMatches($matchQuery, $pageCrawl)) {
    //             $pageCrawl->log('Should run query did not match');
    //             continue;
    //         }

    //         /** @var array<DomQuery> */
    //         $linkQueries = $page['links_queries'];
    //         foreach ($linkQueries as $query) {
    //             $this->extractLinksForQuery($query, $pageCrawl, $followNewLinksOnly);
    //         }
    //     }
    // }

    public function extractLinksForQuery(
        DomQuery $query,
        PageCrawl $pageCrawl,
        bool $followNewLinksOnly = false,
    ): void {
        $nodes = $this->domQueryRunner->runQuery($query, $pageCrawl->domCrawler);

        $pageCrawl->log('Found ' . count($nodes) . ' nodes that match follow links query');
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                // @codeCoverageIgnoreStart
                return;
                // @codeCoverageIgnoreEnd
            }
            $this->extractLinks(
                $query,
                $node,
                $pageCrawl,
                $followNewLinksOnly,
            );
        }
    }

    /**
     * @param DomQuery   $query
     * @param DOMElement $node
     * @param PageCrawl  $pageCrawl
     * @param bool       $followNewLinksOnly
     *
     * @return void
     */
    protected function extractLinks(
        DomQuery $query,
        DOMElement $node,
        PageCrawl $pageCrawl,
        bool $followNewLinksOnly = false,
    ): void {
        // you can specify the attribute that contains the URL (e.g. sometimes it's not a link with href, but javacript)
        $attr = $query['attribute'] ?? 'href';
        $href = $node->getAttribute($attr);
        if (isset($query['transforms']) && $href) {
            $href = $this->stringTransformer->apply($href, $query['transforms']);
        }

        $anchorText = $node->textContent;
        $pageCrawl->log('Found link: ' . $href);

        $this->addLinkToFrontier(
            new UrlFrontierLink(['url' => $href, 'anchor_text' => $anchorText]),
            $pageCrawl,
            $followNewLinksOnly,
        );
    }

    public function addLinkToFrontier(
        UrlFrontierLink $extractedLink,
        PageCrawl $pageCrawl,
        bool $followNewLinksOnly = false,
    ): ?UrlFrontierLink {
        $href = $extractedLink->url;
        $toUri = $this->linkToUri->convertFromFrontier($extractedLink->url, $pageCrawl->getFinalRedirectedUrl());
        if ($followNewLinksOnly && Link::forUrlUid((string) $toUri)->exists()) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $frontierLink = $this->frontierManager->addFromPageCrawl(
            $pageCrawl,
            $href,
            $extractedLink
        );

        return $frontierLink;
    }
}
