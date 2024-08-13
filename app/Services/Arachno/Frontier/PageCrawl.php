<?php

namespace App\Services\Arachno\Frontier;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Storage\MimeTypeManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Page crawl Data Transfer Object.
 */
class PageCrawl
{
    /** @var string|null */
    public ?string $mimeType;

    /** @var DocMetaDto|null */
    protected ?DocMetaDto $docMeta;

    /** @var Doc|null */
    protected ?Doc $doc = null;

    /** @var array<string> */
    protected array $log = [];

    /** @var array<string, mixed> */
    protected array $proxySettings = [];

    /** @var array<string, mixed> */
    protected array $pdfOcrSettings = [];

    /** @var array<string, mixed> */
    protected array $httpSettings = [];

    /**
     * @param Crawler             $domCrawler
     * @param UrlFrontierLink     $pageUrl
     * @param array<string,mixed> $crawlerSettings
     * @param ContentCache|null   $contentCache
     */
    public function __construct(
        public Crawler $domCrawler,
        public UrlFrontierLink $pageUrl,
        public array $crawlerSettings,
        public ?ContentCache $contentCache = null
    ) {
        if (!is_null($contentCache)) {
            $this->setContentCache($contentCache);
        }
        if (!is_null($pageUrl->doc_id)) {
            /** @var Doc */
            $doc = Doc::find($pageUrl->doc_id);
            $this->setDoc($doc);
        }
    }

    public function setContentCache(ContentCache $contentCache): PageCrawl
    {
        $this->contentCache = $contentCache;
        $mimeType = app(MimeTypeManager::class)->extractFromHeaders($contentCache->response_headers ?? []);
        $this->mimeType = $mimeType;

        return $this;
    }

    public function setDoc(Doc $doc): PageCrawl
    {
        $this->doc = $doc;

        return $this;
    }

    public function getDoc(): ?Doc
    {
        return $this->doc;
    }

    /**
     * @param array<string,mixed> $proxySettings
     *
     * @return void
     */
    public function setProxySettings(array $proxySettings): void
    {
        $this->proxySettings = $proxySettings;
    }

    /**
     * @return array<string,mixed>
     */
    public function getProxySettings(): array
    {
        return $this->proxySettings;
    }

    /**
     * @return bool
     */
    public function usesProxy(): bool
    {
        return !empty($this->proxySettings);
    }

    /**
     * @param array<string,mixed> $pdfOcrSettings
     *                                            ['provider' => 'pdfocr', 'languages' => ['eng', 'spa']]
     *
     * @return void
     */
    public function setOcrSettings(array $pdfOcrSettings): void
    {
        $this->pdfOcrSettings = $pdfOcrSettings;
    }

    /**
     * @return array<string,mixed>
     */
    public function getOcrSettings(): array
    {
        return $this->pdfOcrSettings;
    }

    /**
     * @return bool
     */
    public function needsOcr(): bool
    {
        return !empty($this->pdfOcrSettings);
    }

    /**
     * @param array<string,mixed> $httpSettings
     *
     * @return void
     */
    public function setHttpSettings(array $httpSettings): void
    {
        $this->httpSettings = $httpSettings;
    }

    /**
     * @return array<string,mixed>
     */
    public function getHttpSettings(): array
    {
        return $this->httpSettings;
    }

    public function isJson(): bool
    {
        if (!isset($this->mimeType)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return app(MimeTypeManager::class)->isJson($this->mimeType);
    }

    public function isPdf(): bool
    {
        if (!isset($this->mimeType)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        if ($this->mimeType === 'binary/octet-stream' && $this->contentCache) {
            // @codeCoverageIgnoreStart
            if (!is_dir(storage_path('app/tmp'))) {
                mkdir(storage_path('app/tmp'));
            }
            $tmpFile = storage_path('app/tmp') . '/' . Str::random(40);
            file_put_contents($tmpFile, $this->contentCache->response_body);
            $mimeType = mime_content_type($tmpFile);
            $this->mimeType = $mimeType !== false ? $mimeType : null;
            unlink($tmpFile);
            // @codeCoverageIgnoreEnd
        }

        return $this->mimeType ? app(MimeTypeManager::class)->isPdf($this->mimeType) : false;
    }

    public function isDocx(): bool
    {
        if (is_null($this->mimeType)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return app(MimeTypeManager::class)->isDocx($this->mimeType);
    }

    // public function isXml(): bool
    // {
    //     if (is_null($this->mimeType)) {
    //         //@codeCoverageIgnoreStart
    //         return false;
    //         //@codeCoverageIgnoreEnd
    //     }

    //     return app(MimeTypeManager::class)->isXml($this->mimeType);
    // }

    /**
     * @codeCoverageIgnore
     * Helper access function to get the response body of the crawled url.
     *
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->contentCache?->response_body;
    }

    /**
     * Helper access function to get the response json as an associative array.
     *
     * @return array<mixed>
     */
    public function getJson(): array
    {
        $content = $this->contentCache?->response_body ?? '{}';
        $json = json_decode($content, true);

        return $json ?? [];
    }

    /**
     * Helper access function to get the response json of the page crawl that refered to this page,
     * if you know that the referer was JSON.
     *
     * @return array<mixed>
     */
    public function getRefererJson(): array
    {
        $content = $this->pageUrl->refererLink?->contentCache?->response_body ?? '{}';
        $json = json_decode($content, true);

        return $json ?? [];
    }

    /**
     * Helper access function to get the response DOM of the page crawl that refered to this page
     * as a DomCrawler Object.
     *
     * @return Crawler
     */
    public function getRefererDom(): Crawler
    {
        $content = $this->pageUrl->refererLink?->contentCache?->response_body ?? '';

        return new Crawler($content);
    }

    /**
     * If there are redirects, the pageUrl->url is no longer correct and can't be used to generate links.
     * So we need the final URL after all redirects.
     * We've enabled tracking or redirects (https://docs.guzzlephp.org/en/stable/request-options.html#allow-redirects) so we can
     * inspect the header to get the full history.
     *
     * @return string
     */
    public function getFinalRedirectedUrl(): string
    {
        $headers = $this->contentCache?->response_headers ?? [];

        $key = isset($headers['x-guzzle-redirect-history']) ? 'x-guzzle-redirect-history' : 'X-Guzzle-Redirect-History';

        if (isset($headers[$key])) {
            return Arr::last($headers[$key]);
        }

        return $this->pageUrl->url;
    }

    /**
     * Gets all meta data associated with the referer, it's referer and the current pageUrl and groups it together as a DocMetaDto object.
     *
     * @return array<string, mixed>
     */
    public function getFrontierMeta(): array
    {
        $meta = $this->pageUrl->refererLink?->refererLink?->frontierMeta?->meta ?? [];
        $meta = array_merge($meta, $this->pageUrl->refererLink?->frontierMeta?->meta ?? []);
        $meta = array_merge($meta, $this->pageUrl->frontierMeta?->meta ?? []);

        return $meta;
    }

    /**
     * @param string $line
     *
     * @return self
     */
    public function log(string $line): self
    {
        $this->log[] = $line;

        return $this;
    }

    /**
     * @return self
     */
    public function persistLog(): self
    {
        if (!empty($this->log)) {
            $this->pageUrl->frontier_log = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', implode(PHP_EOL, $this->log));
            $this->pageUrl->save();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getSettingVerifySsl(): bool
    {
        return $this->crawlerSettings['verify_ssl'] ?? config('arachno.guzzle_verify_ssl', true);
    }

    /**
     * @return string|null
     */
    public function getSettingUserAgent(): ?string
    {
        return $this->crawlerSettings['user_agent'] ?? config('arachno.default_user_agent');
    }

    /**
     * @return int|null
     */
    public function getSettingThrottleRequests(): ?int
    {
        return $this->crawlerSettings['throttle_requests'] ?? null;
    }

    /**
     * @return array<mixed>
     */
    public function getSettingDefaultCookies(): array
    {
        return $this->crawlerSettings['default_cookies'] ?? [];
    }

    /**
     * @return array<string>
     */
    public function getSettingExcludedCssSelectors(): array
    {
        return $this->crawlerSettings['css_exclude_selectors'] ?? [];
    }

    /**
     * @return string|null
     */
    public function getSettingCss(): ?string
    {
        $css = ' ';
        $crawler = $this->pageUrl->crawl?->crawler;
        if ($crawler && $crawler->source && $crawler->source->css) {
            $css = $crawler->source->css;
        }

        $css .= ' ' . ($this->crawlerSettings['css'] ?? '');

        return trim($css) !== '' ? trim($css) : null;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function getSettingDeleteIfUnused(): bool
    {
        return $this->crawlerSettings['delete_if_unused'] ?? false;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function getSettingForUpdate(): bool
    {
        return ($this->crawlerSettings['for_update'] ?? false)
            || ($this->pageUrl->crawl?->isTypeForUpdates() ?? false);
    }

    /**
     * @return bool
     */
    public function isCatalogueStartUrl(): bool
    {
        return $this->pageUrl->url && $this->pageUrl->url === $this->pageUrl->catalogueDoc?->start_url;
    }
}
