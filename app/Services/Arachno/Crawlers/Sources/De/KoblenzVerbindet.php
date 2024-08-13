<?php

namespace App\Services\Arachno\Crawlers\Sources\De;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use DOMElement;
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: de-koblenz-verbindet
 * title: koblenz.de/
 * url: https://www.koblenz.de/
 */
class KoblenzVerbindet extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'de-koblenz-verbindet',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.koblenz.de/rathaus/verwaltung/ortsrecht-satzungen/',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/verwaltung\/ortsrecht-satzungen/')) {
            $this->handleForUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    public function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.downloads div.downloads__item', function (DOMElement $anchor) use ($pageCrawl) {
            $domCrawler = new DomCrawler($anchor);
            $title = $domCrawler->filter('a .downloads__file-name')->getNode(0)->textContent ?? '';
            $title = $title ? preg_replace('/\.pdf/', '', $title) : '';

            /** @var DOMElement $href */
            $href = $domCrawler->filterXPath('.//a')->getNode(0);
            $href = $href->getAttribute('href');

            $uniqueId = Str::of($href)->after('downloads/')->before('.pdf');
            $docMetaDto = new DocMetaDto();

            $docMetaDto->title = $title;
            $docMetaDto->primary_location = '104040';
            $docMetaDto->source_url = $href;
            $docMetaDto->work_type = 'regulation';
            $docMetaDto->language_code = 'deu';
            $docMetaDto->source_unique_id = 'updates_' . $uniqueId;
            $docMetaDto->download_url = $href;

            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMetaDto;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }
}
