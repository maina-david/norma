<?php

namespace App\Services\Arachno\Crawlers;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Services\Arachno\Arachno;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;

/**
 * @method void                                  customStartCrawl(Crawl $crawl)
 * @method \Symfony\Component\DomCrawler\Crawler crawlUrl(PageCrawl $pageCrawl)
 * */
abstract class AbstractCrawlerConfig implements CrawlerConfigInterface
{
    /** @var array<string, mixed> */
    public $config = [];

    public function __construct(public Arachno $arachno, public Crawler $crawler)
    {
        // $filePath = app_path('Services' . DIRECTORY_SEPARATOR . 'Arachno' . DIRECTORY_SEPARATOR . 'Crawlers' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . $crawler->slug . '.php');
        $this->setSettings();
    }

    /**
     * @codeCoverageIgnore
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     **/
    public function setSettings(?array $config = null): void
    {
        $config = array_merge($this->getDefaultSettings(), $config ?? $this->getCrawlerSettings());

        if (isset($config['start_urls']) && !is_array($config['start_urls'])) {
            throw new InvalidCrawlerConfigException('start_urls has to be an array');
        }
        if (isset($config['css_exclude_selectors']) && !is_array($config['css_exclude_selectors'])) {
            throw new InvalidCrawlerConfigException('css_exclude_selectors has to be an array');
        }
        if (isset($config['css']) && !is_string($config['css'])) {
            throw new InvalidCrawlerConfigException('css has to be a string or null');
        }

        $this->config = $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultSettings(): array
    {
        return [
            'css_exclude_selectors' => [],
            'css' => null,
            'default_cookies' => [],
            'delete_if_unused' => false,
            'only_capture_new_links' => false,
            'start_urls' => [],
            'throttle_requests' => 0,
            'user_agent' => null,
            'verify_ssl' => true,
            'for_update' => false,
        ];
    }

    /**
     * {@inheritDoc}
     **/
    public function setSettingsItem(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Can be extended by implementing config to add additional settings
     * for fetching.
     *
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
    }

    /**
     * {@inheritDoc}
     **/
    public function getStartUrls(CrawlType $crawlType): array
    {
        $startUrls = $this->config['start_urls'] ?? [];
        if (empty($startUrls)) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        return $startUrls['type_' . $crawlType->value];
    }

    /**
     * {@inheritDoc}
     **/
    public function getDefaultCookies(): array
    {
        return $this->config['default_cookies'] ?? [];
    }

    /**
     * {@inheritDoc}
     **/
    public function getUserAgent(): ?string
    {
        return $this->config['user_agent'] ?? config('arachno.default_user_agent');
    }

    /**
     * {@inheritDoc}
     **/
    public function getExcludedCssSelectors(): array
    {
        return $this->config['css_exclude_selectors'] ?? [];
    }

    /**
     * {@inheritDoc}
     **/
    public function getThrottleRequests(): ?int
    {
        return $this->config['throttle_requests'] ?? null;
    }

    /**
     * {@inheritDoc}
     **/
    public function getVerifySsl(): bool
    {
        return $this->config['verify_ssl'] ?? true;
    }

    /**
     * {@inheritDoc}
     **/
    public function getCss(): ?string
    {
        $css = ' ';
        if ($this->crawler->source && $this->crawler->source->css) {
            $css = $this->crawler->source->css;
        }

        $css .= ' ' . ($this->config['css'] ?? '');

        return trim($css) !== '' ? trim($css) : null;
    }

    /**
     * {@inheritDoc}
     **/
    public function getDeleteIfUnused(): bool
    {
        return $this->config['delete_if_unused'] ?? false;
    }

    /**
     * @param array<array<string,mixed>> $fromConfig
     *
     * @return array<DomQuery>
     */
    public function arrayOfQueries(array $fromConfig): array
    {
        $out = [];
        foreach ($fromConfig as $query) {
            $out[] = new DomQuery($query);
        }

        return $out;
    }
}
