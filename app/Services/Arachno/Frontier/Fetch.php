<?php

namespace App\Services\Arachno\Frontier;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Telescope\Telescope;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Fetch
{
    protected ClientInterface $httpClient;

    public function __construct(protected LinkToUri $linkToUri)
    {
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return ContentCache|null
     */
    public function fetchPage(PageCrawl $pageCrawl): ?ContentCache
    {
        $pageUrl = $pageCrawl->pageUrl;
        $requestOptions = [
            'http_settings' => $pageCrawl->getHttpSettings(),
            'proxy_settings' => $pageCrawl->getProxySettings(),
        ];

        return $this->fetchFromCacheOrUrl($pageCrawl, $pageUrl->url, $requestOptions);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param PageCrawl $pageCrawl
     * @param string    $uri
     *
     * @return ContentCache|null
     */
    public function fetchFromPage(PageCrawl $pageCrawl, string $uri): ?ContentCache
    {
        return $this->fetchFromCacheOrUrl($pageCrawl, $uri);
    }

    /**
     * @param PageCrawl           $pageCrawl
     * @param string              $uri
     * @param array<string,mixed> $requestOptions
     *
     * @return ContentCache|null
     */
    public function fetchFromCacheOrUrl(PageCrawl $pageCrawl, string $uri, array $requestOptions = []): ?ContentCache
    {
        $pageUrl = $pageCrawl->pageUrl;
        $url = $this->linkToUri->convertFromFrontier($uri, $pageCrawl->getFinalRedirectedUrl());
        /** @var ContentCache|null */
        $contentCache = ContentCache::where('crawl_id', $pageUrl->crawl_id)
            ->where('url', (string) $url)
            ->first();

        if (!$contentCache) {
            $contentCache = $this->fromUrl($pageCrawl, (string) $url, $requestOptions);
        }

        return $contentCache;
    }

    /**
     * @param string              $method
     * @param string              $url
     * @param array<string,mixed> $guzzleOptions
     *
     * @return ResponseInterface|null
     */
    public function fetch(string $method, string $url, array $guzzleOptions = []): ?ResponseInterface
    {
        $guzzleOptions = array_merge([
            'connect_timeout' => 120,
            'http_errors' => false,
            'allow_redirects' => [
                'max' => 5,
                'strict' => false,
                'referer' => true,
                'protocols' => ['http', 'https'],
                'track_redirects' => true,
            ],
        ], $guzzleOptions);

        /** @var GuzzleHttpClient $client */
        $client = $this->getHttpClient();

        $attempts = 0;
        $maxAttempts = 3;
        while ($attempts < $maxAttempts) {
            try {
                $response = $client->request(strtoupper($method), $url, $guzzleOptions);
                break;
                // @codeCoverageIgnoreStart
            } catch (TransferException $th) {
                $attempts++;
                sleep($attempts * (10 * $attempts));
                // @codeCoverageIgnoreEnd
            }
        }
        if ($attempts === $maxAttempts) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        if ($response->getStatusCode() >= 300) {
            return null;
        }

        return $response;
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        if (!isset($this->httpClient)) {
            // @codeCoverageIgnoreStart
            $this->httpClient = new GuzzleHttpClient();
            // @codeCoverageIgnoreEnd
        }

        return $this->httpClient;
    }

    /**
     * @return void
     */
    public function setHttpClient(ClientInterface $client): void
    {
        $this->httpClient = $client;
    }

    /**
     * @param string              $url
     * @param PageCrawl           $pageCrawl
     * @param array<string,mixed> $requestOptions
     *
     * @return ContentCache|null
     */
    private function fromUrl(PageCrawl $pageCrawl, string $url, array $requestOptions = []): ?ContentCache
    {
        $pageUrl = $pageCrawl->pageUrl;

        if ($pageCrawl->usesProxy()) {
            // @codeCoverageIgnoreStart
            $proxyOptions = $requestOptions['proxy_settings'] ?? [];
            [$headers, $content] = $this->fetchWithProxy($url, $proxyOptions);
        // @codeCoverageIgnoreEnd
        } else {
            /** @var resource */
            $debugStream = fopen('php://temp', 'w');
            $defaultOptions = [];
            $defaultOptions['debug'] = $debugStream; // config('app.debug');
            $defaultOptions['verify'] = $pageCrawl->getSettingVerifySsl();
            $defaultOptions['cookies'] = $requestOptions['http_settings']['options']['cookies'] ?? $this->prepareCookies($pageCrawl);
            $defaultOptions['headers'] = [
                'User-Agent' => $pageCrawl->getSettingUserAgent(),
            ];
            $throttle = $pageCrawl->getSettingThrottleRequests();
            if ($throttle) {
                $c = 0;
                while ($c < 100) {
                    $lastRequest = (int) Cache::get(config('arachno.throttle_cache_key') . ($pageCrawl->pageUrl->crawl_id ?? ''), 0);
                    $now = microtime(true) * 1000;
                    $c++;
                    if (($now - $lastRequest) < $throttle) {
                        // @codeCoverageIgnoreStart
                        usleep(300000);
                    // @codeCoverageIgnoreEnd
                    } else {
                        break;
                    }
                }
            }
            $argOptions = $requestOptions['http_settings']['options'] ?? [];
            $method = $requestOptions['http_settings']['method'] ?? 'GET';
            $options = $defaultOptions;

            foreach ($argOptions as $key => $value) {
                /** @var string $key */
                $options[$key] = $value;
            }
            $response = $this->fetch($method, $url, $options);

            rewind($debugStream);
            if ($debug = stream_get_contents($debugStream)) {
                // @codeCoverageIgnoreStart
                try {
                    $debug = (string) $debug;
                    $pageCrawl->log($debug);
                } catch (Throwable $th) {
                }
                // @codeCoverageIgnoreEnd
            }
            fclose($debugStream);

            Cache::put(
                config('arachno.throttle_cache_key') . ($pageCrawl->pageUrl->crawl_id ?? ''),
                microtime(true) * 1000,
                60
            );
            if (!$response) {
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
            }
            /** @var array<string,mixed> $headers */
            /** @var string $content */
            [$headers, $content] = $this->getHeadersAndContent($response);
        }

        /** @var Crawl */
        $crawl = $pageUrl->crawl;

        return $this->storeToCache($url, $headers, $content, $crawl);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string              $url
     * @param array<string,mixed> $proxyOptions
     *
     * @return array<mixed>
     */
    public function fetchWithProxy(
        string $url,
        array $proxyOptions,
    ): array {
        $provider = $proxyOptions['provider'] ?? 'scraping_bee';

        return match ($provider) {
            'scrapfly' => $this->fetchWithScrapFly($url, $proxyOptions),
            default => $this->fetchWithScrapingBee($url, $proxyOptions),
        };
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string              $url
     * @param array<string,mixed> $proxyOptions
     *
     * @return array<mixed>
     */
    public function fetchWithScrapFly(
        string $url,
        array $proxyOptions = [],
    ): array {
        $params = [
            'key=' . config('services.scrapfly.key'),
            'url=' . urlencode($url),
            'proxified_response=true',
        ];

        $options = $proxyOptions['options'] ?? [];

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $key => $value) {
                $params[] = 'headers[' . strtolower($key) . ']=' . $value;
            }
        }
        foreach ($proxyOptions['options'] ?? [] as $key => $value) {
            if (in_array($key, ['headers'])) {
                continue;
            }
            if (in_array($key, ['retry', 'render_js', 'auto_scroll', 'asp', 'ssl', 'dns', 'cache', 'cache_clear', 'session_sticky_proxy'])) {
                $params[] = $key . '=' . ($value ? 'true' : 'false');
                continue;
            }
            $params[] = $key . '=' . urlencode($value);
        }

        $get = config('services.scrapfly.base_url') . '/scrape?' . implode('&', $params);

        $response = Http::timeout(120)
            ->retry(2, 3000)
            ->get($get);
        $content = $response->body();
        $headers = $response->headers();

        return [$headers, $content];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string              $url
     * @param array<string,mixed> $proxyOptions
     *
     * @return array<mixed>
     */
    public function fetchWithScrapingBee(
        string $url,
        array $proxyOptions = [],
    ): array {
        if (!isset($proxyOptions['options'])) {
            $proxyOptions['options'] = [];
        }
        $proxyOptions['options']['render_js'] = $proxyOptions['options']['render_js'] ?? false;

        $params = [
            'api_key=' . config('services.scraping_bee.key'),
            'url=' . urlencode($url),
        ];

        foreach ($proxyOptions['options'] ?? [] as $key => $value) {
            if (in_array($key, ['js_scenario', 'extract_rules'])) {
                $params[] = $key . '=' . urlencode(json_encode($value) ?: '');
                continue;
            }
            if (in_array($key, ['block_ads', 'block_resources', 'custom_google', 'forward_headers', 'forward_headers_pure', 'json_response', 'premium_proxy', 'render_js', 'screenshot', 'screenshot_full_page', 'stealth_proxy'])) {
                $params[] = $key . '=' . ($value ? 'true' : 'false');
                continue;
            }
            $params[] = $key . '=' . urlencode($value);
        }

        $url = config('services.scraping_bee.base_url') . '?' . implode('&', $params);
        $method = $proxyOptions['method'] ?? 'GET';

        $response = Http::timeout(120)
            ->retry(2, 3000)
            ->{$method}($url);
        $content = $response->body();
        $headers = $response->headers();

        return [$headers, $content];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return CookieJar
     */
    private function prepareCookies(PageCrawl $pageCrawl): CookieJar
    {
        $cookies = $pageCrawl->getSettingDefaultCookies();
        $arr = array_merge($pageCrawl->pageUrl->crawl->cookies ?? [], $cookies);

        $pageHeaders = $pageCrawl->contentCache?->response_headers ?? [];
        $request = new Request('GET', $pageCrawl->pageUrl->url);
        $response = new Response(200, $pageHeaders);
        $jar = new CookieJar(false, $arr);
        $jar->extractCookies($request, $response);

        return $jar;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array<array<string,mixed>|string>
     */
    private function getHeadersAndContent(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();
        $headers = $response->getHeaders();

        return [$headers, $content];
    }

    /**
     * @param string              $url
     * @param array<string,mixed> $headers
     * @param string              $content
     * @param Crawl               $crawl
     *
     * @return ContentCache
     */
    public function storeToCache(
        string $url,
        array $headers,
        string $content,
        Crawl $crawl
    ): ContentCache {
        $cache = new ContentCache([
            'url' => $url,
            'crawl_id' => $crawl->id,
            'response_headers' => $headers,
            'response_body' => $content,
        ]);
        $cache->crawl()->associate($crawl);

        $cache->setUid();
        // attempt to save - there might be a duplicate key, in which case we'll fetch that one
        // but we'll save a trip to the DB if we just attempt to save it, rather than checking if it exists
        try {
            if (config('telescope.enabled')) {
                // @codeCoverageIgnoreStart
                // had a weird issue where some content wasn't being stored by telescope. the QueryWatcher replaceBindings was struggling with some content
                Telescope::stopRecording();
                // @codeCoverageIgnoreEnd
            }
            $cache->save();
            if (config('telescope.enabled')) {
                // @codeCoverageIgnoreStart
                Telescope::startRecording();
                // @codeCoverageIgnoreEnd
            }
            // @codeCoverageIgnoreStart
        } catch (QueryException $th) {
            /** @var ContentCache|null */
            $cache = ContentCache::where('uid', $cache->uid)->first();
            $cache?->crawl()->associate($crawl);
            // @codeCoverageIgnoreEnd
        }

        /** @var ContentCache */
        return $cache;
    }
}
