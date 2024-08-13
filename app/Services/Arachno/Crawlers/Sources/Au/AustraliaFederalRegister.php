<?php

namespace App\Services\Arachno\Crawlers\Sources\Au;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: au-legislation
 * title: legislation.gov.au
 * url: https://www.legislation.gov.au
 */
class AustraliaFederalRegister extends AbstractCrawlerConfig
{
    /**
     * @return array<string>
     */
    protected function handleUrl(): array
    {
        $docTypes = [
            'Act',
            'LegislativeInstrument',
            'NotifiableInstrument',
            'Gazette',
        ];

        $date = now()->format('Y-m-d');

        $urls = [];

        foreach ($docTypes as $docType) {
            $urls[] = new UrlFrontierLink([
                'url' => "https://api.prod.legislation.gov.au/v1/titles/search(criteria='and(event([compilationRegistered,asMadeRegistered],{$date},now),collection({$docType}))')?select=id,name&orderby=asMadeRegisteredAt%20desc&count=true&top=25"]);
        }

        return $urls;
    }

    /**
     * @param mixed $roundedValue
     *
     * @return array<int>
     */
    protected function generateLinks($roundedValue): array
    {
        $interval = 100;
        $resultArray = [];
        for ($i = 0; $i <= $roundedValue; $i += $interval) {
            $resultArray[] = $i;
        }

        return $resultArray;
    }

    /**
     * @return array<string>
     */
    protected function handleStartUrls(): array
    {
        $docTypes = [
            'Act',
            'LegislativeInstrument',
            'NotifiableInstrument',
        ];

        $urls = [];

        foreach ($docTypes as $docType) {
            $urls[] = new UrlFrontierLink([
                'url' => "https://api.prod.legislation.gov.au/v1/titles/search(criteria='and(status(InForce),pointintime(Latest),collection({$docType}))')?\$select=administeringDepartments,collection,hasCommencedUnincorporatedAmendments,id,isInForce,isPrincipal,name,number,optionalSeriesNumber,searchContexts,seriesType,subCollection,year&\$expand=administeringDepartments,searchContexts(\$expand=fullTextVersion)&\$orderby=name%20asc&\$count=true&\$top=100&\$skip=0"]);
        }

        return $urls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'au-legislation',
            'throttle_requests' => 500,
            'css_exclude_selectors' => ['iframe'],
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    ...$this->handleUrl(),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    ...$this->handleStartUrls(),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_browser' => 'networkidle0',
                ],
            ]);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\/latest|\/pdf/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/document_1\/document_1\.html$/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            //            if ($this->arachno->matchUrl($pageCrawl, '/gov\.au\/WhatsNew/')) {
            if ($this->arachno->matchUrl($pageCrawl, '/api\.prod\.legislation\.gov\.au/')) {
                $this->handleForUpdates($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\/latest/')) {
                $this->getDownloadLink($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/pdf/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/api\.prod\.legislation\.gov\.au/')) {
                $this->handlePagination($pageCrawl);
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/document_1\/document_1\.html$/')) {
                $this->handleMeta($pageCrawl);
                $this->handleCapture($pageCrawl);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCapture(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'html > body',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#navPoint_1',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"styles.css") and @rel="stylesheet"]',
            ],
        ]);
        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('a[href]') as $href) {
                /** @var DOMElement $href */
                $href->removeAttribute('href');
            }
            foreach ($crawler->filter('del') as $delElem) {
                /** @var DOMElement $delElem */
                $delElem->setAttribute('style', 'display: none;');
            }
            /** @var DOMElement $tocItem */
            foreach ($crawler->filter('p[class^="TOC"]') as $tocItem) {
                $tocItem->remove();
            }

            return $crawler;
        };

        $this->arachno->capture(
            $pageCrawl,
            bodyQueries: $bodyQueries,
            headQueries: $headQueries,
            removeQueries: [],
            postProcessCssCallable: null,
            preStoreCallable: $preStore,
        );
    }

    protected function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $docMeta = new DocMetaDto();
        $values = $pageCrawl->getJson()['value'] ?? [];

        foreach ($values as $group) {
            $link = "https://www.legislation.gov.au/{$group['id']}/latest/downloads";
            $workType = $this->getWorkType($group['collection']);

            $docMeta->title = $group['name'];
            $docMeta->source_url = $link;
            $docMeta->download_url = $link;
            $docMeta->language_code = 'eng';
            $docMeta->primary_location = '392';
            $docMeta->source_unique_id = "updates_{$group['id']}";
            $docMeta->work_number = $group['id'];
            $docMeta->work_type = $workType;

            $link = new UrlFrontierLink(['url' => $link]);
            $link->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $link, true);
        }
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $values = $pageCrawl->getJson()['value'] ?? [];

        foreach ($values as $group) {
            $workDate = $group['searchContexts']['fullTextVersion']['start'] ?? '';
            $workDate = Str::before($workDate, 'T');
            $workType = $this->getWorkType($group['collection']);
            $workType = $workType;
            $uniqueId = $group['id'];
            $url = "https://www.legislation.gov.au/{$uniqueId}/latest/{$workDate}/text/original/epub/OEBPS/document_1/document_1.html";

            $title = $group['name'];
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'view_url' => $url,
                'start_url' => $url,
                'primary_location_id' => '392',
                'language_code' => 'eng',
            ]);

            try {
                $meta['work_date'] = $workDate;
                $meta['work_type'] = $workType;
            } catch (InvalidFormatException) {
            }

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
        }
    }

    protected function handlePagination(PageCrawl $pageCrawl): void
    {
        $initialUrl = $pageCrawl->pageUrl->url;
        $docType = Str::of($initialUrl)->after('collection(')->before(')');
        $pages = $pageCrawl->getJson()['@odata.count'] ?? [];
        $roundedValue = floor((int) $pages / 100) * 100;
        $skips = $this->generateLinks($roundedValue);

        foreach ($skips as $skip) {
            $l = new UrlFrontierLink([
                'url' => "https://api.prod.legislation.gov.au/v1/titles/search(criteria='and(status(InForce),pointintime(Latest),collection({$docType}))')?\$select=administeringDepartments,collection,hasCommencedUnincorporatedAmendments,id,isInForce,isPrincipal,name,number,optionalSeriesNumber,searchContexts,seriesType,subCollection,year&\$expand=administeringDepartments,searchContexts(\$expand=fullTextVersion)&\$orderby=name%20asc&\$count=true&\$top=100&\$skip={$skip}"]);
            $this->arachno->followLink($pageCrawl, $l);
        }
    }

    protected function getWorkType(string $workType): string
    {
        return match ($workType) {
            'Act' => 'act',
            'LegislativeInstrument', 'NotifiableInstrument' => 'legislative_instrument',
            default => 'gazette'
        };
    }

    protected function mergePDFs(PageCrawl $pageCrawl): void
    {
        $pdfLink = $pageCrawl->domCrawler->filter('.download-list-primary .document-format-pdf a');
        $count = $pdfLink->count();

        $pdfLinks = [];
        $plainDir = sprintf('tmp/%d%s', now()->getTimestampMs(), Str::random(8));
        $workingDir = storage_path("app/{$plainDir}");
        $mergedPdf = "{$workingDir}/merged.pdf";

        Storage::disk('local')->createDirectory($plainDir);

        for ($i = 0; $i < $count; $i++) {
            $response = Http::get($pdfLink->eq($i)->link()->getUri());
            $filename = "{$workingDir}/file_{$i}.pdf";
            $pdfLinks[] = $filename;
            file_put_contents($filename, $response->body());
        }

        // Increase the timeout to 300 seconds
        $process = Process::timeout(300)
            ->run([
                'gs', '-q', '-dNOPAUSE', '-dBATCH', '-sDEVICE=pdfwrite',
                "-sOutputFile={$mergedPdf}",
                ...$pdfLinks,
            ]);

        if ($process->successful()) {
            /** @var string $generated */
            $generated = file_get_contents($mergedPdf);
            $pageCrawl->domCrawler->clear();
            $pageCrawl->domCrawler->addContent($generated, 'application/pdf');
            /** @var ContentCache $contentCache */
            $contentCache = $pageCrawl->contentCache;
            $contentCache->response_body = $generated;

            $this->arachno->capturePDF($pageCrawl);
        }

        Storage::disk('local')->deleteDirectory($plainDir);
    }

    protected function getDownloadLink(PageCrawl $pageCrawl): void
    {
        $domCrawler = $pageCrawl->domCrawler;
        $pdfLink = $domCrawler->filter('.download-list-primary .document-format-pdf a');

        if ($pdfLink->count() > 1) {
            $this->mergePDFs($pageCrawl);

            return;
        }

        $pdfLink = $pdfLink->link()->getUri();

        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pdfLink);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $pdfLink]), true);
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
        $catalogueDoc->load('docMeta');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '392');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $catalogueDoc->docMeta?->doc_meta['work_type'] ?? null);
        $this->arachno->saveDoc($pageCrawl);

        $pageCrawl->setDoc($doc);
    }
}
