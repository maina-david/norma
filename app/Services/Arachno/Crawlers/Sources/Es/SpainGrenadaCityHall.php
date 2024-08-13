<?php

namespace App\Services\Arachno\Crawlers\Sources\Es;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 *  slug: es-granada
 *  title: City of Granada
 *  url: https://www.granada.org/normativa.htm
 */
class SpainGrenadaCityHall extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'es-granada',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.granada.org/inet/wordenanz.nsf/ww6c?readviewentries&collapseview&start=1',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://www.granada.org/inet/wordenanz.nsf/ww6c?readviewentries&collapseview&start=31',
                    ]),
                ],
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => 'https://www.granada.org/rsslegisla.xml']),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/ww6c\?readviewentries&collapseview&start/')
            || $this->arachno->matchUrl($pageCrawl, '/\?readviewentries&start=\d+\.\d+&collapse=\d+\.\d+/')) {
            $this->getLinks($pageCrawl);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\?readviewentries&start=\d+\.\d+.\d+&collapse=\d+\.\d+.\d+/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/wordenanz.nsf\/ww6c\/.+/')) {
            $this->handleMeta($pageCrawl);
            $this->getPageContent($pageCrawl);
            $this->handleToc($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\/rsslegisla\.xml/')) {
            $this->handleForUpdates($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/granada\.org\/inet\/wordenanz\.nsf/')) {
            if ($this->arachno->matchCSS($pageCrawl, 'a[href$="pdf"]')) {
                $this->handlePdfLink($pageCrawl);
            }

            $this->getPageContent($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
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
        $this->arachno->eachX($pageCrawl, '//*[local-name()="item"]', function (DOMElement $item) use ($pageCrawl) {
            $domCrawler = new DomCrawler($item);
            $docMeta = new DocMetaDto();

            /** @var DOMElement $title */
            $title = $domCrawler->filterXPath('//*[local-name()="title"]')->getNode(0);
            $title = Str::of($title->textContent)->before(':');
            $docMeta->title = $title;

            /** @var DOMElement|null $summary */
            $summary = $domCrawler->filterXPath('//head/meta[@name="description"]')->getNode(0);
            $summary = $summary?->getAttribute('content');

            /** @var DOMElement $link */
            $link = $domCrawler->filterXPath('//*[local-name()="link"]')->getNode(0);
            $link = $link->textContent;
            $uniqueId = Str::of($link)->afterLast('/');
            $docMeta->source_unique_id = 'updates_' . $uniqueId;
            $docMeta->source_url = $link;
            $docMeta->work_number = $uniqueId;
            $docMeta->language_code = 'spa';
            $docMeta->primary_location = '164577';
            $docMeta->work_type = $this->getWorkType($title);

            $docMeta->summary = $summary;

            /** @var DOMElement $workDate */
            $workDate = $domCrawler->filterXPath('//*[local-name()="pubDate"]')->getNode(0);

            try {
                $workDate = $workDate->textContent;
                $wrkDate = explode(' ', $workDate);
                $wrkDate = Carbon::parse($wrkDate[1] . '-' . $wrkDate[2] . '-' . $wrkDate[3]);
                $docMeta->work_date = $wrkDate;
            } catch (InvalidFormatException) {
            }

            $link = new UrlFrontierLink(['url' => $link]);
            $link->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $link, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handlePdfLink(PageCrawl $pageCrawl): void
    {
        $pdfLinkElem = $pageCrawl->domCrawler->filter('a[href$="pdf"]');
        $count = $pdfLinkElem->count();

        if ($count > 0) {
            $this->mergePDFs($pageCrawl);

            return;
        }

        $pdfLink = $pdfLinkElem->link()->getUri();

        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pdfLink);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $pdfLink]), true);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @throws FilesystemException
     *
     * @return void
     */
    protected function mergePDFs(PageCrawl $pageCrawl): void
    {
        $pdfLinkElem = $pageCrawl->domCrawler->filter('a[href$="pdf"]');
        $count = $pdfLinkElem->count();

        $pdfLinks = [];
        $plainDir = sprintf('tmp/%d%s', now()->getTimestampMs(), Str::random(8));
        $workingDir = storage_path("app/{$plainDir}");
        $mergedPdf = "{$workingDir}/merged.pdf";

        Storage::disk('local')->createDirectory($plainDir);

        for ($i = 0; $i < $count; $i++) {
            $response = Http::get($pdfLinkElem->eq($i)->link()->getUri());
            $filename = "{$workingDir}/file_{$i}.pdf";
            $pdfLinks[] = $filename;
            file_put_contents($filename, $response->body());
        }

        $output = Process::run([
            'gs', '-q', '-dNOPAUSE', '-dBATCH', '-sDEVICE=pdfwrite', "-sOutputFile={$mergedPdf}", ...$pdfLinks,
        ]);

        if ($output->successful()) {
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

    /**
     * @param string $title
     *
     * @return string
     */
    protected function getWorkType(string $title): string
    {
        return match (explode(' ', strtolower($title))[0]) {
            'acuerdo' => 'agreement',
            'orden' => 'order',
            'resolución' => 'resolution',
            'ley' => 'law',
            default => 'regulation',
        };
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[local-name()="viewentries"]//*[local-name()="viewentry"]',
            function (DOMElement $entry) use ($pageCrawl) {
                $domCrawler = new DomCrawler($entry);

                /** @var DOMElement $viewEntry */
                $viewEntry = $domCrawler->filterXPath('.//*[local-name()="viewentry"]')->getNode(0);
                $uniqueId = $viewEntry->getAttribute('unid');

                /** @var DOMElement $title */
                $title = $domCrawler->filterXPath('.//entrydata/text')->getNode(0)
                    ?? $domCrawler->filterXPath('.//entrydata/textlist/text')->getNode(0);
                $title = $title->textContent;
                $title = explode('[', $title)[0];

                if ($title) {
                    $catDoc = new CatalogueDoc([
                        'title' => $title,
                        'source_unique_id' => $uniqueId,
                        'view_url' => "https://www.granada.org/inet/wordenanz.nsf/ww6c/{$uniqueId}",
                        'start_url' => "https://www.granada.org/inet/wordenanz.nsf/ww6c/{$uniqueId}",
                        'primary_location_id' => '164577',
                        'language_code' => 'spa',
                    ]);

                    $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

                    $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
                }
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[local-name()="viewentries"]//*[local-name()="viewentry"]',
            function (DOMElement $entry) use ($pageCrawl) {
                $domCrawler = new DomCrawler($entry);

                /** @var DOMElement $viewEntry */
                $viewEntry = $domCrawler->filterXPath('.//*[local-name()="viewentry"]')->getNode(0);
                $position = $viewEntry->getAttribute('position');
                $children = $viewEntry->getAttribute('children');

                $catLink = "https://www.granada.org/inet/wordenanz.nsf/ww6c?readviewentries&start={$position}.1&collapse={$position}.1&count={$children}";

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $catLink]), true);
            });
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

        /** @var DOMElement $summary */
        $summary = $pageCrawl->domCrawler->filterXPath('//head/meta[@name="description"]')->getNode(0);
        $summary = $summary->getAttribute('content');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '164577');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'regulation');

        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $summary);

        $downloadUrl = $pageCrawl->domCrawler->filter('a[href$="pdf"]');
        $downloadUrl = $downloadUrl->count() > 0 ? $downloadUrl->link()->getUri() : null;
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);

        /** @var DOMElement $dateElem */
        $dateElem = $pageCrawl->domCrawler->filter('a[href*="ww3?openview&restricttocategory="]')->getNode(0);

        try {
            $date = $dateElem->textContent;
            $date = explode('/', $date);
            $date = Carbon::parse(implode('-', $date));
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $date);
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $date);
        } catch (InvalidFormatException) {
        }
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getPageContent(PageCrawl $pageCrawl): void
    {
        /** @var DOMElement|null $isPdfDoc */
        $isPdfDoc = $pageCrawl->domCrawler->filter('#mibody')->getNode(0);

        if ($isPdfDoc && strlen($isPdfDoc->textContent) < 250) {
            $this->handlePdfLink($pageCrawl);

            return;
        }
        /** @var string $content */
        $content = mb_convert_encoding($pageCrawl->contentCache->response_body ?? '', 'UTF-8', 'latin1');
        /** @var string $content */
        $content = preg_replace('/(<a [^>]+>)<\/font>([^<]+)<font[^>]+><\/a>/', '$1$2</a>', $content);
        $pageCrawl->domCrawler->clear();
        $pageCrawl->domCrawler->addHtmlContent($content);
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#mibody',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'head style',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'a[href$="pdf"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '#indice',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.volverindice',
            ],
            //            [
            //                'type' => DomQueryType::CSS,
            //                'query' => 'img[src$="FieldElemFormat=gif"]',
            //            ],
        ]);

        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('[name]') as $section) {
                /** @var DOMElement $section */
                $section->setAttribute('id', 'l' . $section->getAttribute('name'));
            }

            foreach ($crawler->filter('#indice a') as $anchor) {
                if (empty(trim($anchor->textContent))) {
                    /** @var DOMElement $anchor */
                    $anchor->removeAttribute('href');
                }
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $extractLink = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');
            $href = $href ? explode('#', $href) : '';

            if ($href && $href[1]) {
                return '#l' . $href[1];
            }

            return $href[0] ?? '';
        };

        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');

            return 'l' . Str::of($href)->after('#');
        };

        $criteria = function ($nodeDTO) {
            $emptyNode = trim($nodeDTO['node']->textContent) === '';
            $href = $nodeDTO['node']->getAttribute('href');

            return !$emptyNode && $href !== '';
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            '#indice',
            $criteria,
            extractLinkFunc: $extractLink,
            extractUniqueIdFunc: $extractUniqueId,
        );
    }
}
