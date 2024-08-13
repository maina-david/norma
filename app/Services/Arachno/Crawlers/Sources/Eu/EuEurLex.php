<?php

namespace App\Services\Arachno\Crawlers\Sources\Eu;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\TocItemDraft;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Exception;
use Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Process;
use Storage;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: eu-eur-lex
 * title: Eur-Lex
 * url: https://eur-lex.europa.eu/
 */
class EuEurLex extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        $redirectedUrl = $pageCrawl->getFinalRedirectedUrl();
        $pageCrawl->pageUrl->url = $redirectedUrl;
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/direct-access\.html/')) {
                $this->arachno->followLinksXpath($pageCrawl, '//div[@id="MainContent"]//div[@class="EurlexContent"]//table/tbody/tr[1]/td[2]/a');
            }
            if ($this->arachno->matchUrl($pageCrawl, '/daily-view\/L-series\/default\.html/')) { // Capture update
                $this->arachno->followLinksXpath($pageCrawl, '//div[@class="EurlexContent"]//div[@class="panel-body"]//div[contains(@class, "daily-view-row-spacing")]//a'); // edit this so it captures metadata as well for updates
            }
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/search\.html\?SUBDOM_INIT=CONSLEG&DTS_SUBDOM=CONSLEG&DTS_DOM=EU_LAW&lang=en/')) {
                $this->handleCatalogue($pageCrawl);
            } elseif ($this->arachno->matchUrl($pageCrawl, '/europa\.eu\/eli\/[a-z_]+\/\d{4}/')
             || $this->arachno->matchUrl($pageCrawl, '/eur-lex\.europa\.eu\/legal-content\/EN\/TXT\/\?uri=/')
             || $this->arachno->matchUrl($pageCrawl, '/eur-lex\.europa\.eu\/legal-content\/EN\/ALL\/\?uri=/')) {
                $this->handleCatalogueFromCsv($pageCrawl);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/legal-content\/[A-Z]{2}\/TXT\/PDF\//')) {
            $this->handlePdf($pageCrawl);
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/legal-content\/[A-Z]{2}\/(?:TXT|ALL)\/(?!PDF)/')) {
            $this->captureWork($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/legal-content\/EN\/TXT\/\?uri=/')) {
            $this->getPageContent($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/eur-lex\.europa\.eu\/legal-content\/EN\/(?:TXT|ALL)\/\?uri=/')) {
            $this->handleMeta($pageCrawl);
            $this->handleToc($pageCrawl);
            $this->getPageContent($pageCrawl);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function extractMeta(PageCrawl $pageCrawl): array
    {
        $crawler = $pageCrawl->domCrawler;
        $link = $crawler->filter('div.PubFormats li>a[lang="en"]')->link()->getUri();

        $textUnderTitle = $crawler->filter('#PP1Contents p>em, #document1.tabContent p.reference')->text();
        $language = $this->parseDocLanguage($textUnderTitle);
        $language = trim($language, '()');

        $idLabel = $crawler->filter('p.DocumentTitle')->text();
        if (!$idLabel) {
            throw new Exception('ID text ~ Celex No .. cannot be empty!');
        }
        $idLabel = preg_replace('/[\pZ\pC]/u', ' ', $idLabel) ?? ''; // unicode space to regular space
        $matches = preg_split("/\s+/", $idLabel);
        if (!$matches) {
            throw new Exception('Celex Number didn\'t return a valid match .. Check the matching pattern!');
        }
        $id = $matches[1]; // CELEX No.
        $link = $pageCrawl->getFinalRedirectedUrl();

        $title = $crawler->filter('#PP1Contents #title')->text();
        if (!$title) {
            throw new Exception('Title Not found .. it cannot be empty, check matcher!');
        }

        $metaArr = [
            'title' => $title,
            'language_code' => $this->getLanguageCode($language),
            'source_url' => $link,
            'primary_location' => '9',
            'work_type' => $this->getWorkType($id),
            'source_unique_id' => (string) Str::of($id)->match('/(\d+[A-Z]+\d+)(?:-\d{8})?/'),
            'work_number' => (string) Str::of($id)->match('/\d+[A-Z]+(\d+)/'),
            'download_url' => $link,
        ];

        try {
            $workDate = $this->parseDocDate($textUnderTitle);
            if ($workDate) {
                $metaArr['work_date'] = $workDate;
            }
        } catch (InvalidFormatException) {
        }

        return $metaArr;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function captureWork(PageCrawl $pageCrawl): void
    {
        $crawler = $pageCrawl->domCrawler;
        $docMeta = new DocMetaDto();

        $metaArr = $this->extractMeta($pageCrawl);
        $docMeta->title = $metaArr['title'];
        $docMeta->language_code = $metaArr['language_code'];
        $docMeta->source_url = $metaArr['source_url'];
        $docMeta->primary_location = $metaArr['primary_location'];
        $docMeta->download_url = $metaArr['download_url'];
        $docMeta->work_type = $metaArr['work_type'];
        $docMeta->work_number = $metaArr['work_number'];

        $pdfLink = $crawler->filterXpath("//ul[contains(@class, 'PubFormatPDF')]/li/a[@lang='" . strtolower($docMeta->language_code) . "']");
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $pdfLink->getNode(0)) { // just add PDF link as is, will check if pdf content or html page returned;
            $docMeta->download_url = $pdfLink->link()->getUri(); // won't call capture PDF directly since some works have multiple PDFs. See https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=OJ%3AJOL_2000_329_R_0001_01
            $docMeta->source_unique_id = 'updates_' . $metaArr['source_unique_id'];

            $l = new UrlFrontierLink(['url' => $docMeta->download_url, 'anchor_text' => $docMeta->title]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        } else {
            $docMeta->source_unique_id = $metaArr['source_unique_id'];

            $l = new UrlFrontierLink(['url' => $metaArr['source_url'], 'anchor_text' => $docMeta->title]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getPageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//div[@id="document1"]/div[@class="tabContent"]/div',
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

        $preStore = function (DomCrawler $crawler) {
            /** @var DOMElement $article */
            foreach ($crawler->filter('a[href*="legal-content/EN/AUTO/?uri="], a[href*="#"]') as $article) {
                $article->removeAttribute('href');
                $article->removeAttribute('onclick');
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, preStoreCallable: $preStore);
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
        $langCode = $catalogueDoc->language_code;

        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', $langCode);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $langCode !== 'eng'
            ? $catalogueDoc->title_translation
            : null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '9');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $workType = $this->getWorkType($catalogueDoc);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $selectors = [
            'p[class="title-division-1"]',
            'p[class="title-article-norm"]',
            'p[class="title-article"]',
            'p[class="title-annex-1"]',
        ];
        $toc = [];
        /** @var DOMElement $tocItem */
        foreach ($pageCrawl->domCrawler->filter(implode(',', $selectors)) as $tocItem) {
            $relClass = 'title';
            $tocTitle = str_contains($tocItem->nextElementSibling?->getAttribute('class') ?? '', $relClass)
                ? trim($tocItem->nextElementSibling?->textContent ?? '')
                : '';

            $uniqueId = $tocItem->getAttribute('id');

            $toc[] = new TocItemDraft([
                'href' => $pageCrawl->pageUrl->url . '#' . strtolower($uniqueId),
                'label' => trim($tocItem->textContent) . ' ' . $tocTitle,
                'source_unique_id' => $uniqueId,
                'level' => 1,
            ]);
        }
        $this->arachno->captureTocFromDraftArray($pageCrawl, $toc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @throws FilesystemException
     *
     * @return void
     */
    protected function handlePdf(PageCrawl $pageCrawl): void
    {
        if ($pageCrawl->isPdf()) {
            $this->arachno->capturePDF($pageCrawl);

            return;
        }

        $pdfLinkElem = $pageCrawl->domCrawler->filter('ul.multiStreams li a');
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

    private function readCSV(): Collection
    {
        $content = file_get_contents(__DIR__ . '/fullcrawl_law_list.csv');
        if (!$content) {
            throw new Exception('fullcrawl_law_list.csv file is missing for the Eurlex Script, check repo!');
        }
        /** @var Collection<string> $rows */
        $rows = collect(str_getcsv($content));
        $rows->shift();
        $rows = $rows->map(fn ($row) => trim($row));

        return $rows;
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    public function generateStartUrlsFromCsv(): array
    {
        $rows = $this->readCSV();
        /** @var array<UrlFrontierLink> $catalogueDocLinks */
        $catalogueDocLinks = [];
        foreach ($rows as $row) {
            $catalogueDocLinks[] = new UrlFrontierLink([
                'url' => $row,
            ]);
        }

        return [
            'type_' . CrawlType::FOR_UPDATES->value => [
                new UrlFrontierLink([
                    'url' => 'https://eur-lex.europa.eu/oj/direct-access.html',
                ]),
            ],
            // Eurlex is blocking our crawler on full crawls so docs will be added manually to monolith for now
            // We will read our works from the fullcrawl_laws_list.csv
            // 'type_' . CrawlType::FULL_CATALOGUE->value => [
            //     new UrlFrontierLink([
            //         'url' => 'https://eur-lex.europa.eu/search.html?SUBDOM_INIT=CONSLEG&DTS_SUBDOM=CONSLEG&DTS_DOM=EU_LAW&lang=en&type=advanced&qid=1697167230684', // qid doesn't seem to be expire
            //     ]),
            // ],
            'type_' . CrawlType::FULL_CATALOGUE->value => $catalogueDocLinks,
        ];
    }

    protected function handleCatalogueFromCsv(PageCrawl $pageCrawl): void
    {
        $meta = $this->extractMeta($pageCrawl);
        $catDoc = new CatalogueDoc([
            'title' => $meta['title'],
            'source_unique_id' => $meta['source_unique_id'],
            'view_url' => $meta['source_url'],
            'start_url' => $meta['source_url'],
            'language_code' => $meta['language_code'],
            'primary_location_id' => $meta['primary_location'],
        ]);
        $docMetaFields = [ // The missing fields not added to CatalogueDoc table
            'work_date' => $meta['work_date'],
            'work_number' => $meta['work_number'],
        ];

        $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
            'doc_meta' => $docMetaFields,
        ]);
        $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.SearchResult', function (DOMElement $doc) use ($pageCrawl) {
            $domCrawler = new DomCrawler($doc);

            /** @var DOMElement $anchor */
            $anchor = $domCrawler->filter('h2 a.title')->getNode(0);
            $title = $anchor->textContent;
            $href = $anchor->getAttribute('name');

            $uniqueId = '';
            $meta = [];

            /** @var DOMElement $metaNode */
            foreach ($domCrawler->filter('div.SearchResultData dt') as $metaNode) {
                if (str_contains($metaNode->textContent, 'CELEX')) {
                    $uniqueId = $metaNode->nextElementSibling?->textContent;
                    $meta['work_number'] = $uniqueId;
                }
                if (str_contains($metaNode->textContent, 'Author')
                    && str_contains($metaNode->nextElementSibling?->textContent ?? '', 'Not Found')) {
                    $meta['author'] = $metaNode->nextElementSibling?->textContent;
                }
                if (str_contains($metaNode->textContent, 'Date')) {
                    $workDate = Carbon::createFromFormat('d/m/Y', $metaNode->nextElementSibling?->textContent ?? '');
                    if ($workDate) {
                        $meta['work_date'] = $workDate->format('Y-m-d');
                    }
                }
            }

            if (!$uniqueId) {
                return;
            }

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'view_url' => $href,
                'start_url' => $href,
                'language_code' => 'eng',
                'primary_location_id' => '9',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
            $this->handleNextPage($pageCrawl);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleNextPage(PageCrawl $pageCrawl): void
    {
        $nextPageLink = $pageCrawl->domCrawler->filter('a[title="Next Page"]');
        if ($nextPageLink->getNode(0)) {
            $href = $nextPageLink->link()->getUri();

            $this->arachno->followLink(
                $pageCrawl,
                new UrlFrontierLink(['url' => $href, 'anchor_text' => 'Next Page']),
                true
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'eu-eur-lex',
            'throttle_requests' => 300,
            'start_urls' => $this->generateStartUrlsFromCsv(),
        ];
    }

    /**
     * parseDocLanguage by default returns EN but if not in doc's available languages then returns the first language
     * available.
     *
     * @param string $textUnderTitle
     *
     * @return string
     */
    private function parseDocLanguage(string $textUnderTitle): string
    {
        $language = 'EN'; // default
        $languages = (string) Str::of($textUnderTitle)->match('/(?:\((?:[A-Z]{2}(?:,\s*)?)*\)|—\s*([A-Z]{2})\s*—)/');
        $languagesCodes = array_map('trim', explode(',', $languages));
        $language = in_array('EN', $languagesCodes) ? $language : $languagesCodes[0];

        return $language;
    }

    /**
     * parseDocDate returns correctly formatted year from textUnderTitle.
     *
     * @param string $textUnderTitle
     *
     * @return \Illuminate\Support\Carbon|null
     */
    private function parseDocDate(string $textUnderTitle): ?Carbon
    {
        $dateString = (string) Str::of($textUnderTitle)->match('/(\d{1,2}\.\d{1,2}\.(?:1|2)\d{3})/');
        try {
            $workDate = Carbon::createFromFormat('d.m.Y', $dateString);
            if ($workDate) {
                return $workDate;
            }
        } catch (InvalidFormatException) {
        }

        return null;
    }

    private function getLanguageCode(string $code): string
    {
        return match ($code) {
            'BG' => 'bul',
            'ES' => 'spa',
            'CS' => 'ces',
            'DA' => 'dan',
            'DE' => 'deu',
            'ET' => 'est',
            'EL' => 'ell',
            'EN' => 'eng',
            'FR' => 'fra',
            'GA' => 'gle',
            'HR' => 'hrv',
            'IT' => 'ita',
            'LV' => 'lav',
            'LT' => 'lit',
            'HU' => 'hun',
            'MT' => 'mlt',
            'NL' => 'nld',
            'PL' => 'pol',
            'PT' => 'por',
            'RO' => 'ron',
            'SK' => 'slk',
            'SL' => 'slv',
            'FI' => 'fin',
            'SV' => 'swe',
            default => 'eng'
        };
    }

    /**
     * getWorkType takes celex and returns work type.
     * See: https://eur-lex.europa.eu/content/tools/TableOfSectors/types_of_documents_in_eurlex.html.
     *
     * @param string $celex
     *
     * @return string
     */
    public function getWorkType(string $celex): string
    {
        $celex = trim($celex);
        $sector = (string) Str::of($celex)->match('/^(\w)/');
        $descriptor = (string) Str::of($celex)->match('/^\w\d{4}([A-Z]{1,2})\w+/');
        $corrigenda = (string) Str::of($celex)->match('/(R\(\d+\))$/');

        if ($corrigenda) {
            return 'corrigenda';
        }

        // Match by sector
        if ($sector == '1') {
            return 'treaty';
        }

        $workType = '';
        if ($sector == '2') {
            $workType = match ($descriptor) {
                'A' => 'agreement',
                default => 'act',
            };
        } elseif ($sector == '3') {
            $workType = match ($descriptor) {
                'R' => 'regulation',
                'L' => 'directive',
                'D','S' => 'decision',
                'K' => 'recommendation',
                'A' => 'opinion',
                'G' => 'resolution',
                'C' => 'declaration',
                default => 'law',
            };
        } elseif ($sector == '4') {
            $workType = match ($descriptor) {
                'A' => 'agreement',
                'D' => 'decision',
                'X', 'Y' => 'act',
                default => 'law',
            };
        } elseif ($sector == 'E') {
            $workType = match ($descriptor) {
                'A' => 'agreement',
                'C','G' => 'act',
                default => 'law',
            };
        } else {
            $workType = 'law';
        }

        return $workType;
    }
}
