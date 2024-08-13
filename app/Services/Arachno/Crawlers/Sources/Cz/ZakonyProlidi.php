<?php

namespace App\Services\Arachno\Crawlers\Sources\Cz;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\TocItemDraft;
use App\Stores\Corpus\ContentResourceStore;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: cz-zakony-prolidi
 * title: Collection of laws of the Czech Republic
 * url: https://www.zakonyprolidi.cz/
 */
class ZakonyProlidi extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        $date = Carbon::now()->format('Y-m');
        $year = Carbon::now()->format('Y');

        return [
            'slug' => 'cz-zakony-prolidi',
            'throttle-requests' => 100,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://www.zakonyprolidi.cz/cs/sbirka']),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/nove-predpisy/{$date}"]),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/nove-ucinne/{$date}"]),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/nova-zneni/{$date}"]),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/zrusene/{$date}"]),
                    new UrlFrontierLink(['url' => 'https://www.zakonyprolidi.cz/obor/pracovni-pravo?page=1']),
                    new UrlFrontierLink(['url' => 'https://www.zakonyprolidi.cz/obor/spravni-pravo?page=1']),
                ],

                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/rocnik/{$year}"]),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/nove-predpisy/{$date}"]),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/nove-ucinne/{$date}"]),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/nova-zneni/{$date}"]),
                    new UrlFrontierLink(['url' => "https://www.zakonyprolidi.cz/cs/zrusene/{$date}"]),
                    new UrlFrontierLink(['url' => 'https://www.zakonyprolidi.cz/obor/pracovni-pravo']),
                    new UrlFrontierLink(['url' => 'https://www.zakonyprolidi.cz/obor/spravni-pravo']),
                ],
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if (($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsForUpdates($pageCrawl)) && !$this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/obor/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/obor/')) {
            $this->checkPageAndSetHttpSettings($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/obor/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_for' => '.DocGrid',
                    'js_scenario' => [
                        'instructions' => [
                            ['wait' => 2000],
                            ['evaluate' => "ChangeSort('sortby', '4');"],
                            ['wait' => 2000],
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * Extract the saved page.
     *
     * @param PageCrawl $pageCrawl
     *
     * @return int
     */
    protected function extractPageFromURL(PageCrawl $pageCrawl): int
    {
        return (int) (explode('page=', $pageCrawl->pageUrl->url)[1] ?? '1');
    }

    /**
     * Set the http settings.
     *
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function checkPageAndSetHttpSettings(PageCrawl $pageCrawl): void
    {
        // Does not select Od nejnovějšího with value 4 and instead Dle počtu zobrazení with value 2 is selected
        $page = $this->extractPageFromURL($pageCrawl);

        $settings = [
            'method' => 'POST',
            'options' => [
                'form_params' => [
                    '__EVENTTARGET' => 'X$idBody$Grid',
                    '__EVENTARGUMENT' => $page,
                    '__VIEWSTATE' => 'EVDwvLVjy6QhZo/y01K/Da1DkfW95z4yuEoOy+OCAd7biUXL0HvoA0/1SGJHx5IaTewRFMUss8MOb3m9Lr76eoBJX8jZgh09WzWlc6pVgwxgjfsXPN/rHa5ilnbbF8iAN5K+BaigOZVSExuIUoagWfInLi8=',
                    '__VIEWSTATEGENERATOR' => '37832EE7',
                    'text' => '',
                    'X$idBody$idSort$idSelect' => 4,
                    'X$idBody$ctl00$idSize' => 'SIZE50',
                ],
            ],
        ];

        if ($page !== 1) {
            $settings['options']['form_params']['__VIEWSTATEGENERATOR'] = $pageCrawl->pageUrl->anchor_text;
        }

        $pageCrawl->setHttpSettings($settings);
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/cs\/sbirka/')) {
                $this->arachno->followLinksCSS($pageCrawl, '.GradeGrid tbody tr td a[href]', true);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/cs\/rocnik/')
                || $this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/obor\//')) {
                //                if ($this->arachno->matchCSS($pageCrawl, 'a.command.next:not(.disabled)') && !$this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/obor\/(pracovni-pravo|spravni-pravo)\?page=\d+/')) {
                //                    $this->generateLinks($pageCrawl);
                //                }
                $this->handleChronologicalCatalogue($pageCrawl);
            }

            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            $this->captureContent($pageCrawl);
            if ($this->arachno->matchCSS($pageCrawl, '.Tree')) {
                $this->captureToc($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/cs\/rocnik/')) {
                $this->handleUpdates($pageCrawl, '.DocGrid tbody tr');
                $this->captureContent($pageCrawl);
                if ($this->arachno->matchCSS($pageCrawl, '.Tree')) {
                    $this->captureToc($pageCrawl);
                }
            }

            $this->handleUpdates($pageCrawl, '.RulesGrid tbody tr:not([class="Line"])');
            $this->captureContent($pageCrawl);
            if ($this->arachno->matchCSS($pageCrawl, '.Tree')) {
                $this->captureToc($pageCrawl);
            }
        }
    }

    protected function followToNextPage(PageCrawl $pageCrawl): void
    {
        $hasNext = $pageCrawl->domCrawler->filter('a.command.next:not(.disabled)')->getNode(0);

        if (!$hasNext) {
            return;
        }

        /** @var DOMElement|null $state */
        $state = $pageCrawl->domCrawler->filter('#Main input[name=__VIEWSTATE]')->getNode(0);
        $page = $this->extractPageFromURL($pageCrawl);
        $nextPage = $page + 1;
        $nextPage = str_replace("page={$page}", "page={$nextPage}", $pageCrawl->pageUrl->url);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $nextPage, 'anchor_text' => $state?->getAttribute('value')]));
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $selector
     *
     * @return void
     */
    protected function followNextPage(PageCrawl $pageCrawl, string $selector): void
    {
        $previousBtn = $pageCrawl->domCrawler->filter($selector)->getNode(0);

        if ($previousBtn) {
            $btnLink = $pageCrawl->domCrawler->filter($selector)->link()->getUri();

            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $btnLink]));
        }
    }

    /**
     * @param PageCrawl             $pageCrawl
     * @param ?array<string, mixed> $meta
     *
     * @return void
     */
    protected function addToCatalogue(PageCrawl $pageCrawl, ?array $meta): void
    {
        if ($meta) {
            $catalogueDoc = new CatalogueDoc([
                'title' => $meta['title'],
                'source_unique_id' => $meta['source_unique_id'],
                'start_url' => $meta['start_url'],
                'view_url' => $meta['view_url'],
                'language_code' => 'ces',
                'primary_location_id' => 175489,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catalogueDoc);
        }
    }

    /**
     * @param PageCrawl  $pageCrawl
     * @param DOMElement $element
     *
     * @return ?array<string, mixed>
     */
    protected function fetchChronologicalDocumentMeta(PageCrawl $pageCrawl, DOMElement $element): ?array
    {
        $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://www.zakonyprolidi.cz');
        $meta = [];

        if ($crawler->filter('td a[class="TT REF_DEL"]')->getNode(0)) {
            return null;
        }

        $link = $crawler->filter('td a')->link()->getUri();
        /** @var DOMElement $title */
        $title = $crawler->filter('td.c2')->getNode(0);
        $title = $title->textContent;
        $uniqueId = Str::of($link)->after('.cz/');

        /** @var DOMElement $date */
        $date = $crawler->filter('td.c3')->getNode(0);
        $date = $date->textContent ?? '';
        preg_match('/([0-9]{2}\.[0-9]{2}\.[0-9]{4})/', $date, $matches);
        $date = $matches[1] ?? null;
        $date = empty($date) ? [] : explode('.', $date);

        try {
            $date = !empty($date) ? trim($date[2]) . '-' . $date[1] . '-' . $date[0] : null;
            $meta['effective_date'] = Carbon::parse($date);
        } catch (InvalidFormatException) {
        }
        $meta['title'] = $title;
        $meta['source_unique_id'] = $uniqueId;
        $meta['start_url'] = $link;
        $meta['view_url'] = $link;

        return $meta;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleChronologicalCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.DocGrid tbody tr', function (DOMElement $element) use ($pageCrawl) {
            $meta = $this->fetchChronologicalDocumentMeta($pageCrawl, $element);

            $this->addToCatalogue($pageCrawl, $meta);
        });

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/obor/')) {
            // its back and we need the view state
            $this->followToNextPage($pageCrawl);
        }
    }

    /**
     * @param PageCrawl  $pageCrawl
     * @param DOMElement $element
     *
     * @return ?array<string, mixed>
     */
    protected function fetchDocumentMetadata(PageCrawl $pageCrawl, DOMElement $element): ?array
    {
        $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://www.zakonyprolidi.cz');
        $meta = [];

        if ($crawler->filter('td a[class="TT REF_DEL"]')->getNode(0)) {
            return null;
        }

        $link = $crawler->filter('td > a')->link()->getUri();
        $titleElem = $crawler->filter('td a')->getNode(0);
        /** @var DOMElement $tableData */
        $tableData = $titleElem?->parentNode;
        $title = $tableData->nextElementSibling;
        $title = $title?->textContent;
        $uniqueId = Str::of($link)->after('.cz/');

        $meta['title'] = $title;
        $meta['source_unique_id'] = $uniqueId;
        $meta['start_url'] = $link;
        $meta['view_url'] = $link;

        return $meta;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    // string $cssSelector
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        if (!$pageCrawl->domCrawler->filter('.RulesGrid tbody tr:not([class="Line"])')->getNode(0)) {
            if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchCSS($pageCrawl, '.GridMonths td.Months a.btn.btn-secondary.command.prev')) {
                $this->followNextPage($pageCrawl, '.GridMonths td.Months a.btn.btn-secondary.command.prev');
            }
        }

        $this->arachno->each($pageCrawl, '.RulesGrid tbody tr:not([class="Line"])', function (DOMElement $element) use ($pageCrawl) {
            $meta = $this->fetchDocumentMetadata($pageCrawl, $element);

            $this->addToCatalogue($pageCrawl, $meta);

            if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
                $this->followNextPage($pageCrawl, '.GridMonths td.Months a.btn.btn-secondary.command.prev');
            }
        });
    }

    /**
     * @param PageCrawl            $pageCrawl
     * @param array<string, mixed> $meta
     * @param DocMetaDto           $docMeta
     *
     * @return void
     */
    protected function addToDocMetaDto(PageCrawl $pageCrawl, array $meta, DocMetaDto $docMeta): void
    {
        $docMeta->title = $meta['title'];
        $docMeta->source_unique_id = "updates_{$meta['source_unique_id']}";
        $docMeta->source_url = $meta['start_url'];
        $docMeta->language_code = 'ces';
        $docMeta->primary_location = '175489';
        $docMeta->work_type = 'regulation';
        $docMeta->effective_date = $meta['effective_date'] ?? null;
        $link = new UrlFrontierLink(['url' => $docMeta->source_url]);
        $link->_metaDto = $docMeta;
        $this->arachno->followLink($pageCrawl, $link, true);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $element
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl, string $element): void
    {
        $this->arachno->each($pageCrawl, $element, function (DOMElement $element) use ($pageCrawl) {
            $docMeta = new DocMetaDto();

            if ($this->arachno->matchUrl($pageCrawl, '/zakonyprolidi\.cz\/cs\/rocnik/')) {
                $meta = $this->fetchChronologicalDocumentMeta($pageCrawl, $element);
                if ($meta) {
                    $this->addToDocMetaDto($pageCrawl, $meta, $docMeta);
                }
            }
            $meta = $this->fetchDocumentMetadata($pageCrawl, $element);

            if ($meta) {
                $this->addToDocMetaDto($pageCrawl, $meta, $docMeta);
            }
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'ces');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 175489);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);

        /** @var DOMElement|null $summary */
        $summary = $pageCrawl->domCrawler->filterXPath('*//head/meta[@property="og:description"]')->getNode(0);
        $summary = $summary?->getAttribute('content');
        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $summary ?? null);

        /** @var DOMElement|null $workDate */
        $workDate = $pageCrawl->domCrawler->filterXPath('//div[@class="doc-meta"]//tr//td[contains(text(), "Platnost od")]')->getNode(0);
        $workDate = $workDate && $workDate->nextElementSibling ? $workDate->textContent : null;
        preg_match('/([0-9]{2}\.[0-9]{2}\.[0-9]{4})/', $workDate ?? '', $matches);
        /** @var ?string $workDate */
        $workDate = $matches[1] ?? null;
        $workDate = $workDate ? explode('.', $workDate) : null;

        /** @var DOMElement|null $effDate */
        $effDate = $pageCrawl->domCrawler->filterXPath('//div[@class="doc-meta"]//tr//td[contains(text(), "Účinnost od")]')->getNode(0);
        $effDate = $effDate && $effDate->nextElementSibling ? $effDate->textContent : null;
        preg_match('/([0-9]{2}\.[0-9]{2}\.[0-9]{4})/', $effDate ?? '', $matches);
        /** @var ?string $effDate */
        $effDate = $matches[1] ?? null;
        $effDate = $workDate ? explode('.', $effDate ?? '') : null;

        try {
            $workDate = !empty($workDate) ? trim($workDate[2]) . '-' . $workDate[1] . '-' . $workDate[0] : null;
            $workDate = Carbon::parse($workDate);

            $effectiveDate = !empty($effDate) ? trim($effDate[2]) . '-' . $effDate[1] . '-' . $effDate[0] : null;
            $effectiveDate = Carbon::parse($effectiveDate);

            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $effectiveDate);
        } catch (InvalidFormatException) {
        }

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'regulation');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function captureContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.Frags',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
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
            $externalLinks = $crawler->filter('a.linkfile');

            if ($externalLinks->count() > 0) {
                $contentResourceStore = app(ContentResourceStore::class);

                /** @var DOMElement $externalLink */
                foreach ($externalLinks as $externalLink) {
                    $href = $externalLink->getAttribute('href');

                    if (str_contains($href, '.pdf')) {
                        $pdf = Http::get($href);
                        $resource = $contentResourceStore->storeResource($pdf, 'application/pdf');
                        $newHref = $contentResourceStore->getLinkForResource($resource);
                        $externalLink->setAttribute('href', $newHref);
                    }

                    $pdf = Http::get($href);
                    $resource = $contentResourceStore->storeResource($pdf, 'image/png');
                    $newHref = $contentResourceStore->getLinkForResource($resource);
                    $externalLink->setAttribute('href', $newHref);
                }

                $otherExtLinks = $crawler->filter('a[class="extern"]');

                /** @var DOMElement $otherExtLink */
                foreach ($otherExtLinks as $otherExtLink) {
                    $otherExtLink->removeAttribute('href');
                }

                $tables = $crawler->filter('table');

                /** @var DOMElement $table */
                foreach ($tables as $table) {
                    $table->removeAttribute('style');
                }
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, preStoreCallable: $preStore);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<string, mixed>
     */
    protected function fetchTocContent(PageCrawl $pageCrawl): array
    {
        /** @var DOMElement $header */
        $header = $pageCrawl->domCrawler->filter('h1')->getNode(0);
        $docId = $header->getAttribute('data-docid');
        $sliceId = $header->getAttribute('data-sliceid');

        $url = 'https://www.zakonyprolidi.cz/handler/rule.ashx?Cmd=SumAll';
        $response = Http::asForm()->post($url, [
            'DocId' => $docId,
            'SliceId' => $sliceId,
            'Text' => '',
            'OnlyFID' => 'false',
        ]);

        return $response->json();
    }

    /**
     * @param array<string, mixed> $tree
     * @param int                  $level
     *
     * @return array<int|string, mixed>
     */
    protected function extractNodes(array $tree, int $level = 1): array
    {
        $nodes = [];

        /** @var array<int|string, mixed> $node */
        foreach ($tree as $node) {
            $nodes[] = [
                'tocTitle' => $node[4],
                'uniqueId' => $node[3],
                'level' => $level,
            ];

            $last = last($node);

            if (is_array($last)) {
                $children = $this->extractNodes($last, $level + 1);
                $nodes = [...$nodes, ...$children];
            }
        }

        return $nodes;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function captureToc(PageCrawl $pageCrawl): void
    {
        $tocs = [];
        $content = $this->fetchTocContent($pageCrawl);
        $data = $content['Data'];
        $baseUrl = $pageCrawl->pageUrl->url;

        $tocItems = $this->extractNodes($data);

        foreach ($tocItems as $tocItem) {
            $tocs[] = new TocItemDraft([
                'href' => "{$baseUrl}/{$tocItem['uniqueId']}",
                'label' => $tocItem['tocTitle'],
                'level' => $tocItem['level'],
                'source_unique_id' => $tocItem['uniqueId'],
            ]);
        }

        $this->arachno->captureTocFromDraftArray($pageCrawl, $tocs);
    }
}
