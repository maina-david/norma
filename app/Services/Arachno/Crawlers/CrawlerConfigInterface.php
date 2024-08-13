<?php

namespace App\Services\Arachno\Crawlers;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\PageCrawl;

/**
 * @property Crawler $crawler
 */
interface CrawlerConfigInterface
{
    /**
     * @return array<string,mixed>
     */
    public function getCrawlerSettings(): array;

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void;

    /**
     * @param array<string,mixed>|null $config
     *
     * @return void
     */
    public function setSettings(?array $config = null): void;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function setSettingsItem(string $key, mixed $value): void;

    /**
     * @param CrawlType $crawlType
     *
     * @return array<UrlFrontierLink>
     */
    public function getStartUrls(CrawlType $crawlType): array;

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string;

    /**
     * @return array<string,mixed>
     */
    public function getDefaultCookies(): array;

    /**
     * @return array<string>
     */
    public function getExcludedCssSelectors(): array;

    /**
     * @return string|null
     */
    public function getCss(): ?string;

    /**
     * @return int|null
     */
    public function getThrottleRequests(): ?int;

    /**
     * @return bool
     */
    public function getVerifySsl(): bool;

    /**
     * @return bool
     */
    public function getDeleteIfUnused(): bool;
}
