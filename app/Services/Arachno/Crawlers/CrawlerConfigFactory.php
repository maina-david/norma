<?php

namespace App\Services\Arachno\Crawlers;

use App\Models\Arachno\Crawler;
use App\Services\Arachno\Arachno;
use Illuminate\Support\Str;

class CrawlerConfigFactory
{
    public function __construct(protected Arachno $arachno)
    {
    }

    public function createConfigForCrawler(Crawler $crawler): AbstractCrawlerConfig
    {
        if (!$crawler->class_name) {
            $slugPrefix = Str::before($crawler->slug ?? '', '-');
            $className = config('arachno_configs.' . $slugPrefix . '.' . ($crawler->slug ?? ''));
            if ($className) {
                /** @var AbstractCrawlerConfig $config */
                $config = new $className($this->arachno, $crawler);

                return $config;
            }

            // @codeCoverageIgnoreStart
            return new DefaultCrawlerConfig($this->arachno, $crawler);
            // @codeCoverageIgnoreEnd
        }
        // @codeCoverageIgnoreStart
        $className = sprintf('App\\Services\\Arachno\\Crawlers\\Sources\\%s', $crawler->class_name);
        /** @var AbstractCrawlerConfig */
        $config = new $className($this->arachno, $crawler);

        return $config;
        // @codeCoverageIgnoreEnd
    }
}
