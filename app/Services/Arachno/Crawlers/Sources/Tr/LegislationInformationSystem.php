<?php

namespace App\Services\Arachno\Crawlers\Sources\Tr;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 *
 * slug: tr-mevzuat
 * title: Presidency of the Republic of Turkey LEGISLATION INFORMATION SYSTEM
 * url: https://mevzuat.gov.tr/
 */
class LegislationInformationSystem extends AbstractCrawlerConfig
{
    protected int $pageSize = 100;

    /**
     * @return int
     */
    protected function getLastPage(): int
    {
        $response = retry(4, function () {
            return Http::timeout(3000)->post('https://mevzuat.gov.tr/anasayfa/MevzuatDatatable', $this->getPagesPayload());
        }, 3000);

        $responseData = $response->json();

        // default to one page.
        return (int) ceil(($responseData['recordsFiltered'] ?? $this->pageSize) / $this->pageSize);
    }

    /**
     * @return array<string>
     */
    protected function getAllPages(): array
    {
        $urls = [];
        $lastPage = $this->getLastPage();

        for ($i = 2; $i <= $lastPage; $i++) {
            $urls[] = "https://mevzuat.gov.tr/anasayfa/MevzuatDatatable?page={$i}";
        }

        return $urls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'tr-mevzuat',
            'throttle_requests' => 400,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://mevzuat.gov.tr/anasayfa/MevzuatDatatable?page=1']),
                ],
                'type_' . CrawlType::NEW_CATALOGUE_WORK_DISCOVERY->value => [
                    new UrlFrontierLink([
                        'url' => 'https://mevzuat.gov.tr/anasayfa/MevzuatDatatable?page=1',
                    ]),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/mevzuat\.gov\.tr\/anasayfa\/MevzuatDatatable/')) {
            $this->handleIndex($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
                $this->handleMeta($pageCrawl);
                $this->arachno->capturePDF($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/mevzuat\.gov\.tr\/anasayfa\/MevzuatFihristDetayIframe/')) {
                $this->handleMeta($pageCrawl);
                $this->handleContent($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl)) {
            $this->handleNewWorks($pageCrawl);
        }
    }

    /**
     * @param int $draw
     *
     * @return array<string, mixed>
     */
    protected function getPagesPayload(int $draw = 1): array
    {
        return [
            'draw' => $draw,
            'columns' => [
                [
                    'data' => null,
                    'name' => '',
                    'searchable' => true,
                    'orderable' => false,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ],
                ],
                [
                    'data' => null,
                    'name' => '',
                    'searchable' => true,
                    'orderable' => false,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ],
                ],
                [
                    'data' => null,
                    'name' => '',
                    'searchable' => true,
                    'orderable' => false,
                    'search' => [
                        'value' => '',
                        'regex' => false,
                    ],
                ],
            ],
            'order' => [
            ],
            'start' => $this->pageSize * ($draw - 1),
            'length' => $this->pageSize,
            'search' => [
                'value' => '',
                'regex' => false,
            ],
            'parameters' => [
                'AranacakIfade' => '',
                'AranacakYer' => 'Baslik',
                'TamCumle' => false,
                'MevzuatTur' => 0,
                'GenelArama' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getPostData(int $draw = 1): array
    {
        return [
            'method' => 'POST',
            'options' => [
                'json' => $this->getPagesPayload($draw),
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/mevzuat\.gov\.tr\/anasayfa\/MevzuatDatatable/')
            && $this->arachno->matchUrl($pageCrawl, '/page=(\d+)/')
        ) {
            preg_match('/page=(\d+)/', $pageCrawl->pageUrl->url, $matches);

            $settings = $this->getPostData((int) $matches[1]);
            $pageCrawl->setHttpSettings($settings);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function addTheOtherPages(PageCrawl $pageCrawl): void
    {
        foreach ($this->getAllPages() as $page) {
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $page, 'anchor_text' => 'Next']));
        }
    }

    public function handleIndex(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $data = $json['data'] ?? [];
        foreach ($data as $item) {
            $this->handleCatalogue($pageCrawl, $item);
        }

        if (str_ends_with($pageCrawl->pageUrl->url, '?page=1')) {
            $this->addTheOtherPages($pageCrawl);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     * @param array<string,mixed>                      $item
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl, array $item): void
    {
        $title = $item['mevAdi'];
        $workNumber = $item['resmiGazeteSayisi'];
        $url = str_contains($item['url'], 'mevzuat.gov.tr') ? $item['url'] : "https://mevzuat.gov.tr/{$item['url']}";
        $uniqueId = str_contains($item['url'], 'mevzuat.gov.tr') ? Str::of($item['url'])->after('.tr')->before('.pdf') : $item['url'];

        $mevzuatTur = Str::of($url)->after('MevzuatTur=')->before('&');
        $mevzuatNo = Str::of($url)->after('MevzuatNo=')->before('&');
        $mevzuatTertip = Str::of($url)->after('MevzuatTertip=');
        $startUrl = str_contains($url, '.pdf') ? $url : "https://mevzuat.gov.tr/anasayfa/MevzuatFihristDetayIframe?MevzuatTur={$mevzuatTur}&MevzuatNo={$mevzuatNo}&MevzuatTertip={$mevzuatTertip}";

        $workDate = $item['kabulTarih'];

        $publicationDate = $item['resmiGazeteTarihi'];

        try {
            $workDate = explode('.', $workDate);
            $workDate = implode('-', $workDate);
            $workDate = Carbon::parse($workDate);
        } catch (InvalidFormatException) {
        }

        $effectiveDate = $item['resmiGazeteTarihi'];

        try {
            $effectiveDate = explode('.', $effectiveDate);
            $effectiveDate = implode('-', $effectiveDate);
            $effectiveDate = Carbon::parse($effectiveDate);
        } catch (InvalidFormatException) {
        }

        $downloadUrl = "https://www.mevzuat.gov.tr/MevzuatMetin/{$mevzuatTur}.{$mevzuatTertip}.{$mevzuatNo}.pdf";

        $catDoc = new CatalogueDoc([
            'title' => $title,
            'source_unique_id' => $uniqueId,
            'language_code' => 'tur',
            'primary_location_id' => 42387,
            'start_url' => $startUrl,
            'view_url' => $url,
        ]);

        $meta = [
            'work_date' => $workDate,
            'effective_date' => $effectiveDate,
            'work_number' => $workNumber,
            'download_url' => $downloadUrl,
            'publication_number' => $publicationDate,
        ];

        $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

        CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
            'doc_meta' => $meta,
        ]);
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'tur');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 42387);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->docMeta?->doc_meta['download_url'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['work_number'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'publication_date', $catalogueDoc->docMeta?->doc_meta['publication_number'] ?? null);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleDocImages(PageCrawl $pageCrawl): void
    {
        $images = $pageCrawl->domCrawler->filter('img[src*="image"]');

        if ($images->count() > 0) {
            /** @var DOMElement $img */
            foreach ($pageCrawl->domCrawler->filter('img[src*="image"]') as $img) {
                $imgSource = str_contains($img->getAttribute('src'), 'https://mevzuat.gov.tr/')
                    ? $img->getAttribute('src')
                    : "https://mevzuat.gov.tr/mevzuatmetin/{$img->getAttribute('src')}";
                $img->setAttribute('src', $imgSource);
            }
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleContent(PageCrawl $pageCrawl): void
    {
        $pageCrawl->domCrawler->clear();
        /** @var ContentCache $contentCache */
        $contentCache = $pageCrawl->contentCache;
        $pageCrawl->domCrawler->addHtmlContent($contentCache->response_body ?? '');
        $this->arachno->each($pageCrawl, 'meta[http-equiv="Content-Type"]', fn ($node) => $node->remove());

        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'body',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//style',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"]',
            ],
        ]);

        $preStore = function (Crawler $crawler) {
            $tableData = $crawler->filter('.MsoNormalTable tr td:not([style*="solid"])');
            /** @var DOMElement $tdData */
            foreach ($tableData as $tdData) {
                $tdData->setAttribute('style', 'border-color: transparent !important;');
            }

            $externalHrefs = $crawler->filter('a[name]');

            /** @var DOMElement $externalHref */
            foreach ($externalHrefs as $externalHref) {
                $externalHref->removeAttribute('name');
                $href = $externalHref->getAttribute('href');

                if (str_contains($href, '#_ftn')) {
                    $href = explode('#_', $href)[1];
                    $externalHref->setAttribute('href', "#{$href}");
                    $externalHref->setAttribute('target', '_top');
                }
            }

            $docLinks = $crawler->filter('a[href*="docx"], a[href*="doc"]');

            foreach ($docLinks as $docLink) {
                /** @var DOMElement $docLink */
                $docLink->removeAttribute('href');
            }

            return $crawler;
        };

        $this->handleDocImages($pageCrawl);

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, preStoreCallable: $preStore);
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleNewWorks(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $data = $json['data'] ?? [];
        $uniqueIds = [];
        foreach ($data as $item) {
            $uniqueIds[] = str_contains($item['url'], 'mevzuat.gov.tr') ? Str::of($item['url'])->after('.tr')->before('.pdf') : $item['url'];
        }
        $docs = CatalogueDoc::where('source_id', $pageCrawl->pageUrl->crawler?->source_id ?? 67)
            ->whereIn('source_unique_id', $uniqueIds)
            ->get()
            ->keyBy('source_unique_id');
        foreach ($data as $item) {
            $id = str_contains($item['url'], 'mevzuat.gov.tr') ? Str::of($item['url'])->after('.tr')->before('.pdf') : $item['url'];
            if (!isset($docs[$id])) {
                $this->handleCatalogue($pageCrawl, $item);
            }
        }
    }
}
