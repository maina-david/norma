<?php

namespace App\Services\Arachno\Crawlers\Sources\Pl;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * @codeCoverageIgnore
 *
 * slug: pl-edziennik-mazowieckie
 * title: Official Journal of the Mazowieckie Voivodeship
 * url: http://edziennik.mazowieckie.pl
 */
class PolandJournalMazowieckiego extends AbstractCrawlerConfig
{
    protected string $baseLink = 'http://edziennik.mazowieckie.pl/';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'pl-edziennik-mazowieckie',
            'throttle' => 500,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => 'http://edziennik.mazowieckie.pl/api/positions?year=' . now()->year . '&month=' . now()->month . '&isList=true']),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/api\/positions/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/GetActPdf\.ashx/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();

        collect($json['Positions'] ?? [])
            ->take(-100)
            ->each(function ($item) use ($pageCrawl) {
                /** @var string|null $title */
                $title = $item['Title'];

                /** @var string|null $uniqueId */
                $uniqueId = $item['Oid'] . '/' . $item['Year'];

                $url = $item['PdfUrl'];
                $url = $this->baseLink . $url;
                $workType = $item['LegalActType'];

                $year = $item['Year'];
                $position = $item['Position'];

                $workDate = $item['ActDate'];
                $effDate = $item['PublicationDate'];

                $docMeta = new DocMetaDto();
                $docMeta->title = empty($title) ? $item['Subject'] : $title;
                $docMeta->source_unique_id = empty($uniqueId) ? $item['CaseNumber'] : $uniqueId;
                $docMeta->download_url = $url;
                $docMeta->source_url = "{$this->baseLink}legalact/{$year}/{$position}";
                $docMeta->work_type = $this->handleWorkType($workType);
                $docMeta->language_code = 'pol';
                $docMeta->primary_location = '160926';

                try {
                    $docMeta->work_date = Carbon::parse($workDate);
                    $docMeta->effective_date = Carbon::parse($effDate);
                } catch (InvalidFormatException) {
                }

                $link = new UrlFrontierLink(['url' => $docMeta->download_url]);
                $link->_metaDto = $docMeta;
                $this->arachno->followLink($pageCrawl, $link, true);
            });
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function handleWorkType(string $type): string
    {
        return match (strtolower($type)) {
            'rozstrzygnięcie nadzorcze' => 'decision',
            'obwieszczenie' => 'notice',
            'porozumienie' => 'agreement',
            'rozporządzenie porządkowe, zarządzenie' => 'ordinance',
            'ogłoszenie' => 'announcement',
            default => 'resolution'
        };
    }
}
