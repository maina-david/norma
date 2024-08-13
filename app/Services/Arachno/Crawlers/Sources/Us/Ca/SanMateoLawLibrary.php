<?php

namespace App\Services\Arachno\Crawlers\Sources\Us\Ca;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: us-law-cityofsanmateo
 * title: City of San Mateo Law Library
 * url: https://law.cityofsanmateo.org/
 */
class SanMateoLawLibrary extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        $year = Carbon::now()->format('Y');

        return [
            'slug' => 'us-law-cityofsanmateo',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => "https://law.cityofsanmateo.org/us/ca/cities/san-mateo/ordinances/{$year}"]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://law.cityofsanmateo.org/us/ca/cities/san-mateo/ordinances']),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if (!$this->arachno->matchUrl($pageCrawl, '/\/ca\/cities\/san-mateo\/ordinances\/\d{4}/')) {
                $this->fetchInitialLinks($pageCrawl);
            }

            if (!$this->arachno->matchUrl($pageCrawl, '/\/ca\/cities\/san-mateo\/ordinances\/\d{4}\//')) {
                $this->handleCatalogue($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/law\.cityofsanmateo\.org\/us\/ca\/cities\/san-mateo\/ordinances/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchMetaData(PageCrawl $pageCrawl): array
    {
        $meta = [];
        $this->arachno->eachX($pageCrawl, '*//a[contains(text(), "Ord. No")]', function (DOMElement $element) use ($pageCrawl, &$meta) {
            $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://law.cityofsanmateo.org/');

            /** @var DOMElement $title */
            $title = $crawler->filterXPath('.//a')->getNode(0);
            $title = $title->textContent;

            $link = $crawler->filterXPath('.//a')->link()->getUri();

            $uri = Str::of($link)->after('ordinances/');
            $someUri = str_contains($uri, '-') ? Str::of($link)->afterLast('/') : $uri;
            $uri = explode('/', $someUri);
            $year = str_contains($someUri, '-') ? Str::of($someUri)->before('-') : $uri[0];
            $uri = implode('-', $uri);
            $pdfLink = "https://law.cityofsanmateo.org/us/ca/cities/san-mateo/ordinances/{$year}/adopted/{$uri}.pdf";

            $uniqueId = Str::of($link)->after('san-mateo/')->before('.pdf');

            $meta[] = [
                'title' => $title,
                'url' => $link,
                'download_url' => $pdfLink,
                'source_unique_id' => $uniqueId,
            ];
        });

        return $meta;
    }

    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $docMetaDto = new DocMetaDto();
        $metaData = $this->fetchMetaData($pageCrawl);

        foreach ($metaData as $meta) {
            $docMetaDto->title = $meta['title'];
            $docMetaDto->source_unique_id = "updates_{$meta['source_unique_id']}";
            $docMetaDto->source_url = $meta['url'];
            $docMetaDto->download_url = $meta['download_url'];
            $docMetaDto->primary_location = '186505';
            $docMetaDto->language_code = 'eng';
            $docMetaDto->work_type = 'ordinance';

            $l = new UrlFrontierLink(['url' => $meta['download_url']]);
            $l->_metaDto = $docMetaDto;
            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function fetchInitialLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '*//nav[@class="toc"]/ul/li/a[contains(@href, "san-mateo/ordinances/")]',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://law.cityofsanmateo.org/');
                $url = $crawler->filterXPath('.//a')->link()->getUri();

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url]), true);
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $metaData = $this->fetchMetaData($pageCrawl);

        foreach ($metaData as $meta) {
            $catDoc = new CatalogueDoc([
                'title' => $meta['title'],
                'source_unique_id' => $meta['source_unique_id'],
                'start_url' => $meta['download_url'],
                'view_url' => $meta['url'],
                'primary_location_id' => 186505,
                'language_code' => 'eng',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 186505);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'ordinance');

        try {
            $url = $catalogueDoc->view_url ?? '';
            $year = Str::of($url)->after('ordinances/')->before('/');
            $date = Carbon::parse("01-01-{$year}");

            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $date);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $date);
        } catch (InvalidFormatException) {
        }
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
