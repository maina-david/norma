<?php

namespace App\Services\Arachno\Frontier;

use App\Models\Arachno\Link;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriNormalizer;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\UriInterface;

class LinkToUri
{
    public const LINK_PREFIX = '/l/';

    /**
     * @param string      $uri
     * @param string|null $baseUri
     *
     * @return UriInterface
     */
    public function convert(string $uri, ?string $baseUri = null): UriInterface
    {
        $uri = new Uri($uri);
        $baseUri = $baseUri ? new Uri($baseUri) : new Uri($uri->getScheme() . '://' . $uri->getAuthority());

        return UriNormalizer::normalize(UriResolver::resolve($baseUri, $uri));
    }

    /**
     * @param string $uri
     * @param string $pageUrl
     *
     * @return UriInterface
     */
    public function convertFromFrontier(string $uri, string $pageUrl): UriInterface
    {
        $baseUri = new Uri($pageUrl);

        return $this->convert($uri, (string) $baseUri);
    }

    public function generateHashedLink(string $href, string $pageUrl): string
    {
        $uri = $this->convertFromFrontier($href, $pageUrl);
        $url = (string) $uri;
        $uid = Link::buildUid($url);
        $hash = Link::hashUid($uid);

        return $this->hashToLink($hash, $url);
    }

    public function hashToLink(string $hash, string $url): string
    {
        $encoded = urlencode($url);

        return static::LINK_PREFIX . $hash . '?l=' . $encoded;
    }
}
