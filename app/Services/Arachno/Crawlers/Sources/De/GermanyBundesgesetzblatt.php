<?php

namespace App\Services\Arachno\Crawlers\Sources\De;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * @codeCoverageIgnore
 * slug: de-bundesgesetzblatt
 * title: Germany Bundesgesetzblatt
 * url: https://www.recht.bund.de/rss/feeds/rss_bgbl-1-2.xml
 */
class GermanyBundesgesetzblatt extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/rss_bgbl(?:-[12])+\.xml/')) {
            $this->handleForUpdates($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/regelungstext\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $entries = $pageCrawl->domCrawler->filterXPath('//*[local-name()="item"]');
        for ($i = $entries->count() - 1; $i >= 0; $i--) {
            $entry = $entries->eq($i);
            $href = $entry->filterXPath('//*[local-name()="link"]')->getNode(0)?->textContent ?? '';
            if (!$href) {
                return;
            }
            $id = str_replace('https://www.recht.bund.de/eli/bund', '', $href);
            $title = $entry->filterXPath('//*[local-name()="title"]')->getNode(0)?->textContent ?? '';
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = $id;
            $docMeta->title = $title;
            $docMeta->language_code = 'deu';
            $docMeta->source_url = $href;
            $docMeta->primary_location = '42320';
            $docMeta->work_number = str_replace('/bgbl_2', '', $id);

            $workType = Str::of($entry->filterXPath('//meta:typ')->getNode(0)?->textContent ?? '')->trim();
            $docMeta->work_type = $this->getWorkTypeEn($workType);

            $date = $entry->filterXPath('//*[local-name()="pubDate"]')->getNode(0)?->textContent ?? '';
            if ($date) {
                try {
                    $docMeta->work_date = Carbon::parse($date);
                } catch (Throwable $th) {
                }
            }
            $pre = $entry->filterXPath('//meta:initiant')->getNode(0)?->textContent ?? '';
            $description = $entry->filterXPath('//*[local-name()="description"]')->getNode(0)?->textContent ?? '';
            $suf = $entry->filterXPath('//meta:sachgebiet')->getNode(0)?->textContent ?? '';
            $docMeta->summary = Str::of($pre . PHP_EOL . $description . PHP_EOL . $suf)->trim();

            $parts = explode('/', $id);
            $allParts = [...explode('-', $parts[1]), $parts[2], $parts[3]];
            $pdfUrl = 'https://www.recht.bund.de/' . implode('/', $allParts) . '/regelungstext.pdf?__blob=publicationFile&v=2';
            $docMeta->download_url = $pdfUrl;

            $l = new UrlFrontierLink(['url' => $pdfUrl, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    /**
     * @param string $workType
     *
     * @return string
     */
    public function getWorkTypeEn(string $workType): string
    {
        return match (strtolower($workType)) {
            'verordnung' => 'ordinance',
            'gesetz' => 'act',
            'sonstiges' => 'notice',
            default => 'notice',
        };
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'de-bundesgesetzblatt',
            'throttle_requests' => 100,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.recht.bund.de/rss/feeds/rss_bgbl-1-2.xml',
                    ]),
                ],
            ],
        ];
    }
}
