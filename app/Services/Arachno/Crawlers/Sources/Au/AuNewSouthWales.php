<?php

namespace App\Services\Arachno\Crawlers\Sources\Au;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 * slug: au-nsw
 * title: Australia New South Wales
 * url: https://legislation.nsw.gov.au/gazette
 */
class AuNewSouthWales extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/legacy\.legislation\.nsw\.gov\.au\/\!\/gazettes\/\d+/')) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'au-nsw',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://legacy.legislation.nsw.gov.au/!/gazettes/' . now()->format('Y'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        foreach ($json as $items) {
            // Loop through the array of items
            foreach ($items as $item) {
                $docMeta = new DocMetaDto();
                $href = $item['pdf'];
                $docMeta->source_unique_id = strtolower(Str::of($href)->after('id=')->replace('.pdf', ''));
                $docMeta->language_code = 'eng';
                $docMeta->title = $item['title'];
                $docMeta->work_type = 'gazette';
                $docMeta->primary_location = '393';
                $downloadUrl = 'https://gazette.legislation.nsw.gov.au' . $href;
                $docMeta->source_url = $downloadUrl;
                $docMeta->download_url = $downloadUrl;
                $date = $item['date'];
                if ($date) {
                    try {
                        $docMeta->work_date = Carbon::parse($date);
                    } catch (InvalidFormatException $th) {
                    }
                }
                $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'PDF']);
                $l->_metaDto = $docMeta;
                $this->arachno->followLink($pageCrawl, $l, true);
            }
        }
    }
}
