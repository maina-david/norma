<?php

namespace App\Services\Arachno\Crawlers;

use App\Services\Arachno\Frontier\PageCrawl;

class DefaultCrawlerConfig extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [];
    }
}
