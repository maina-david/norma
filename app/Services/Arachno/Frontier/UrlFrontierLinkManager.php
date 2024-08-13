<?php

namespace App\Services\Arachno\Frontier;

use App\Contracts\Arachno\UrlFrontierLinkInterface;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Arachno\UrlFrontierMeta;
use App\Services\Arachno\Parse\DocMetaDto;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class UrlFrontierLinkManager
{
    /**
     * @param LinkToUri $linkToUri
     */
    public function __construct(protected LinkToUri $linkToUri)
    {
    }

    /**
     * @param UriInterface             $uri
     * @param UrlFrontierLinkInterface $urlLink
     *
     * @return UrlFrontierLink
     */
    public function addToFrontier(UriInterface $uri, UrlFrontierLinkInterface $urlLink): UrlFrontierLink
    {
        /** @var UrlFrontierLink $urlLink */
        $this->validate($urlLink);

        /** @var string */
        $uri = strtok((string) $uri, '#');

        $tmpFrontierLink = new UrlFrontierLink(['crawl_id' => $urlLink->crawl_id, 'url' => $uri]);
        $tmpFrontierLink->setUid();

        /** @var UrlFrontierLink|null $url */
        $url = UrlFrontierLink::findUid($tmpFrontierLink->uid_hash);

        if ($url) {
            // @codeCoverageIgnoreStart
            // don't add to frontier if we've already added a URL in this crawl
            Log::debug('Url has already been crawled in this crawl: ' . $url->url);

            return $url;
            // @codeCoverageIgnoreEnd
        }

        /** @var UrlFrontierLink $url */
        $url = $urlLink->replicate();
        $url->url = $uri;
        $url->crawl_type = $urlLink->crawl_type;
        $url->setUid();
        $url->save();

        return $url;
    }

    /**
     * @param PageCrawl       $pageCrawl
     * @param string          $uri
     * @param UrlFrontierLink $link
     *
     * @return UrlFrontierLink
     */
    public function addFromPageCrawl(
        PageCrawl $pageCrawl,
        string $uri,
        UrlFrontierLink $link
    ): UrlFrontierLink {
        $fromPageUrl = $pageCrawl->pageUrl;
        $uri = $this->replaceVariables($uri);
        $toUri = $this->linkToUri->convertFromFrontier($uri, $pageCrawl->getFinalRedirectedUrl());

        $link->fill([
            'crawl_id' => $fromPageUrl->crawl_id,
            'crawl_type' => $fromPageUrl->crawl?->crawl_type,
            'referer' => $pageCrawl->getFinalRedirectedUrl(),
            'doc_id' => $pageCrawl->getDoc()?->id,
            'catalogue_doc_id' => $fromPageUrl->catalogue_doc_id,
            'needs_browser' => $fromPageUrl->crawl?->crawler?->needsBrowser() ?? false,
        ]);

        $link->referer_id = $fromPageUrl->id;

        $frontierLink = $this->addToFrontier(
            $toUri,
            $link
        );

        if (!is_null($link->_metaDto)) {
            $this->addMetaToLink($frontierLink, $link->_metaDto);
        }

        return $frontierLink;
    }

    public function replaceVariables(string $url): string
    {
        // e.g. https://example.com/{{NOW:Y}}/updates is replaced with https://example.com/2022/updates
        preg_match_all('/\{\{NOW\:([^\}]+)\}\}/', $url, $matches);
        foreach ($matches[0] as $key => $match) {
            $now = now()->format($matches[1][$key]);
            $url = str_replace($match, $now, $url);
        }

        return $url;
    }

    public function addMetaToLink(UrlFrontierLink $link, DocMetaDto $docMetaDto): UrlFrontierMeta
    {
        /** @var UrlFrontierMeta */
        $meta = $link->frontierMeta ?? UrlFrontierMeta::firstOrCreate(['url_frontier_link_id' => $link->id]);
        $metaDetails = $meta->meta ?? [];
        foreach ($docMetaDto->toArray() as $key => $val) {
            $metaDetails[$key] = $val;
        }
        $meta->meta = $metaDetails;

        $meta->save();

        return $meta;
    }

    /**
     * @param UrlFrontierLinkInterface $urlLink
     *
     * @return void
     */
    private function validate(UrlFrontierLinkInterface $urlLink): void
    {
        /** @var UrlFrontierLink $urlLink */
        if (is_null($urlLink->crawl_id)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('crawl_id is required');
            // @codeCoverageIgnoreEnd
        }
    }
}
