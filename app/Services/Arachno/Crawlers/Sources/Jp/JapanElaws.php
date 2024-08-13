<?php

namespace App\Services\Arachno\Crawlers\Sources\Jp;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Throwable;

/**
 * @codeCoverageIgnore
 * slug: jp-elaws-gov
 * title: e-government portal
 * url: https://elaws.e-gov.go.jp
 *
 * We took a different approach with the catalogue crawl, as there is a CSV file that can be downloaded from
 * here https://elaws.e-gov.go.jp/download/ (it's a zip file that contains a CSV at the root level).
 * We use that CSV to populate the catalogue. So occasionally we will have to update the CV that is in this folder (all_law_list.csv)
 */
class JapanElaws extends AbstractCrawlerConfig
{
    // These are the headers in the CSV
    // 法令種別 : law type
    // 法令番号 : law number
    // 法令名 : law name
    // 法令名読み : name of law
    // 公布日 : promulgation date
    // 改正法令公布日 : Revised law promulgation date
    // 施行日 : enforcement date
    // 法令ID : law id
    // 本文URL : body url
    // 未施行 : not enforced

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'jp-elaws-gov',
            'throttle_requests' => 100,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://elaws.e-gov.go.jp/',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/law\?lawid=/')) {
            $this->handleFetchWork($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/toc\?lawid=/')) {
            $this->captureToc($pageCrawl);
        }
    }

    private function readCSV(): Collection
    {
        $content = file_get_contents('./app/Services/Arachno/Crawlers/Sources/Jp/all_law_list.csv');
        $content = mb_convert_encoding($content ?: '', 'utf8', 'sjis');
        $rows = collect(explode(PHP_EOL, $content));
        $rows = $rows->map(fn ($row) => explode(',', $row));

        return $rows;
    }

    private function getIDIndex(Collection $header): int
    {
        return (int) $header->search(fn ($item) => str_contains($item, 'ID'));
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $rows = $this->readCSV();

        $header = collect($rows->shift());

        $titleIndex = $header->search('法令名');
        $urlIndex = $header->search(fn ($item) => str_contains($item, 'URL'));
        $idIndex = $this->getIDIndex($header);
        /** @var Collection $rows */
        $rows = $rows->filter(fn ($row) => isset($row[$urlIndex]))->values();

        foreach ($rows as $row) {
            $finalUrl = (string) Str::of($row[$urlIndex])
                ->replace('document?', 'law?')
                ->before('_');
            $catDoc = new CatalogueDoc([
                'title' => $row[$titleIndex],
                'source_unique_id' => $row[$idIndex],
                'view_url' => str_replace('law?', 'document?', $finalUrl),
                'start_url' => $finalUrl,
                'language_code' => 'jpn',
                'primary_location_id' => '153618',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        }
    }

    protected function handleFetchWork(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $rows = $this->readCSV();
        $header = collect($rows->shift());
        $idIndex = $this->getIDIndex($header);
        $urlIndex = $header->search(fn ($item) => str_contains($item, 'URL'));
        /** @var Collection $rows */
        $rows = $rows->filter(fn ($row) => isset($row[$urlIndex]) && $row[$idIndex] === $catalogueDoc->source_unique_id)->values();
        $row = $rows->first();
        $typeIndex = $header->search('法令種別');
        $workNumberIndex = $header->search('法令番号');
        $workDateIndex = $header->search('施行日');
        $workType = $this->getJapanWorkType($row[$typeIndex]);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'jpn');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $row[$workNumberIndex] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '153618');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        try {
            /** @var Carbon */
            $workDate = Carbon::createFromFormat('j M Y', trim($row[$workDateIndex]));
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate->format('Y-m-d'));
        } catch (Throwable $th) {
            // throw $th;
        }

        $this->handleCapture($pageCrawl);

        $url = $pageCrawl->getFinalRedirectedUrl();
        $url = preg_replace('/law\?/', 'toc?', $url);
        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url]));
    }

    private function getJapanWorkType(string $type): string
    {
        return match ($type) {
            '勅令', '政令' => 'decree',
            '法律' => 'law',
            '府省令' => 'ordinance',
            '規則' => 'rule',
            '憲法' => 'constitution',
            default => 'law',
        };
    }

    protected function handleCapture(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'body',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'div[class*="_div_TOC"]',
            ],
        ]);

        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('a.fig_pdf_icon') as $section) {
                /** @var DOMElement $section */
                $section->textContent = 'pdf';
            }

            return $crawler;
        };
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
            preStoreCallable: $preStore
        );
    }

    protected function captureToc(PageCrawl $pageCrawl): void
    {
        $baseUrl = preg_replace('/toc\?/', 'law?', $pageCrawl->pageUrl->url);
        $extractLink = function ($nodeDTO) use ($baseUrl) {
            $href = $nodeDTO['node']->getAttribute('href');
            $href = str_replace('#', '', $href);

            return $baseUrl . '#' . $href;
        };

        $extractUniqueId = function ($nodeDTO) {
            return str_replace('#', '', $nodeDTO['node']?->getAttribute('href'));
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            'body > div',
            null,
            null,
            $extractLink,
            $extractUniqueId
        );
    }
}
