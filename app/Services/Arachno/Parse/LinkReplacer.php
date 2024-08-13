<?php

namespace App\Services\Arachno\Parse;

use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class LinkReplacer
{
    /**
     * @param LinkToUri $linkToUri
     */
    public function __construct(protected LinkToUri $linkToUri)
    {
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param bool      $withHashes
     *
     * @return DomCrawler
     */
    public function capture(PageCrawl $pageCrawl, bool $withHashes = true): DomCrawler
    {
        foreach ($pageCrawl->domCrawler->filter('body a[href]') as $link) {
            /** @var DOMElement $link */
            if (!$href = $link->getAttribute('href')) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $baseUrl = $pageCrawl->getFinalRedirectedUrl();
            try {
                $uri = $this->linkToUri->convertFromFrontier($href, $baseUrl);
                // @codeCoverageIgnoreStart
            } catch (MalformedUriException $th) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            $link->setAttribute('target', '_blank');
            if ($uri->getHost() !== (new Uri($baseUrl))->getHost()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $newHref = $withHashes ? $this->linkToUri->generateHashedLink($href, $baseUrl) : (string) $uri;
            $link->setAttribute('href', $newHref);
            if ($withHashes) {
                $link->setAttribute('data-href', $href);
            }
        }

        return $pageCrawl->domCrawler;
    }
}
