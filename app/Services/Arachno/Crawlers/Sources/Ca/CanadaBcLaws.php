<?php

namespace App\Services\Arachno\Crawlers\Sources\Ca;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: ca-bc-laws
 * title: Canada, Province of British Columbia (bclaws.gov.bc.ca)
 * url: https://www.bclaws.gov.bc.ca/
 */
class CanadaBcLaws extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/bcgaz2\/bcgaz2\/.*\?xsl=\/templates\/browse\.xsl/')) {
                $this->handleForUpdatesIndex($pageCrawl);
            }
            if ($this->arachno->matchXpath($pageCrawl, '//a[contains(@href, "Licence.html")]')
                && $this->arachno->matchUrl($pageCrawl, '/document\/id\/bcgaz2\/bcgaz2\/[\w-]+/')
                && !$this->arachno->matchUrl($pageCrawl, '/\/browse\.xsl$/')) {
                $this->handleMeta($pageCrawl);
                $this->capturePageContent($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/civix\/content\/oic\/oic_cur\/\?xsl=\/templates/')
                || $this->arachno->matchUrl($pageCrawl, '/civix\/content\/mo\/mo\/\?xsl=\/templates/')) {
                $this->getYearLink($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/civix\/content\/oic\/oic_cur\/\d+\/\?xsl=\/templates/')) {
                $this->getLatestUpdates($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/civix\/content\/oic\/oic_cur\/\d+\/[0-9_]+\/\?xsl=\/templates/')
                || $this->arachno->matchUrl($pageCrawl, '/civix\/content\/mo\/mo\/\d+\/\?xsl=\/templates/')) {
                $this->handleForUpdates($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/[0-9_]+$/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/civix\/content\/complete\/statreg\/\d+\/\?xsl=/')
                || $this->arachno->matchUrl($pageCrawl, '/civix\/content\/complete\/statreg\/\?xsl=/')
                && !$this->arachno->matchCSS($pageCrawl, '.search-results.statutes-regulations li a[class="shopping-cart"]')) {
                $this->getInitialLinks($pageCrawl);
            }

            if ($this->arachno->matchCSS($pageCrawl, '.search-results.statutes-regulations li a[class="shopping-cart"]')
                || $this->arachno->matchUrl($pageCrawl, '/\d+\/\d+\/\?xsl=\/templates\/browse.xsl/')) {
                $this->followNestedLinks($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && !$this->arachno->matchUrl($pageCrawl, '/\?xsl=\/templates\/browse\.xsl$/')) {
            $this->handleCivixMeta($pageCrawl);
            $this->capturePageContent($pageCrawl);
            $this->handleToc($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleForUpdatesIndex(PageCrawl $pageCrawl): void
    {
        $crawler = $pageCrawl->domCrawler;
        $currentPage = $crawler
            ->filterXPath('//div[@id="breadcrumbs"]//li[last()]/a')
            ->text();
        if ($currentPage == 'Gazette Part II') {
            $latestUpdates = $crawler
                ->filterXPath('//div[@class="main-content"]/ul[contains(@class, "search-results")]/li/a[not(contains(., "– Index" ))]')->first();
            $href = $latestUpdates->link()->getUri();
            $year = $latestUpdates->text();

            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => 'Current Year Updates - ' . $year]);
            $this->arachno->followLink($pageCrawl, $l);

            return;
        }

        $links = $crawler->filterXpath('//div[@class="main-content"]/ul[contains(@class, "search-results")]/li[a[not(contains(., "– Index" ))]]');
        for ($i = $links->count() - 1; $i >= 0; $i--) {
            $wrapper = $links->eq($i);
            $link = $wrapper->filterXPath('descendant-or-self::a')->first();
            $href = $link->link()->getUri();
            $text = $link->text();

            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => $text]);
            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        $url = $pageCrawl->getFinalRedirectedUrl();
        $id = (string) Str::of($url)
            ->match('/document\/id\/bcgaz2\/(bcgaz2\/[\w-]+)$/');
        $workNumber = (string) Str::of($url)
            ->match('/document\/id\/bcgaz2\/bcgaz2\/[\w-]+_([\w-]+)$/');
        $year = (string) Str::of($url)
            ->match('/(\d+)$/');

        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $workNumber);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'notice');
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '2386');
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');

        $title = $dom->filter('head > title')->getNode(0)?->textContent;
        $centeredTitle = $dom->filter('p.CentredTitle')->getNode(0)?->textContent;
        $centeredTitle = $centeredTitle ? ' ' . $centeredTitle : '';
        $fullTitle = $title . $centeredTitle;
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $fullTitle);

        $intro = $dom->filter('body > table:nth-of-type(1) table td:nth-of-type(2)')->getNode(0)?->textContent;
        if ($intro) {
            $dateString = trim(explode("\n", $intro)[1]);
            try {
                if (str_ends_with($dateString, ',')) {
                    $dateString .= $year;
                }
                $workDate = Carbon::createFromFormat('M d, Y', $dateString);
                if ($workDate) {
                    $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate->format('Y-m-d'));
                }
            } catch (InvalidFormatException $th) {
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getInitialLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.search-results.statutes-regulations li.stat-reg-folder a',
            function (DOMElement $links) use ($pageCrawl) {
                $linkText = trim($links->textContent);
                if (!str_contains($linkText, 'Repealed') && !str_contains($linkText, 'Table of Legislative ')) {
                    $domCrawler = new DomCrawler($links);

                    /** @var DOMElement $anchorElem */
                    $anchorElem = $domCrawler->filterXPath('.//a')->getNode(0);
                    $href = 'https://www.bclaws.gov.bc.ca/civix/content/complete/statreg/' . $anchorElem->getAttribute('href');

                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $href]), true);
                }
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followNestedLinks(PageCrawl $pageCrawl): void
    {
        $hasToc = false;
        $this->arachno->each(
            $pageCrawl,
            '.search-results.statutes-regulations li:not(a[class*="shopping-cart"])',
            function (DOMElement $link) use (&$hasToc, $pageCrawl) {
                if ($hasToc) {
                    return;
                }

                $text = strtolower($link->textContent);

                $isAct = str_contains($text, ' act');
                $isReg = str_contains($text, ' regulation');
                $isRepealed = str_contains($text, 'repealed');
                $isPointInTime = str_contains($text, 'point in time');
                $isToc = str_contains($text, 'table of contents');

                if (!$isRepealed && !$isPointInTime && ($isAct || $isReg)) {
                    if ($isToc) {
                        $hasToc = true;
                    }

                    $domCrawler = new DomCrawler($link, $pageCrawl->pageUrl->url, $pageCrawl->pageUrl->url);
                    $url = $domCrawler->filterXPath('.//a')->link()->getUri();
                    /** @var DOMElement $anchorElem */
                    $anchorElem = $domCrawler->filterXPath('.//a')->getNode(0);

                    // / its nested so follow
                    if (str_ends_with($url, '?xsl=/templates/browse.xsl')) {
                        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url]));

                        return;
                    }

                    // its a link to the end document
                    $type = $isAct ? 'act' : 'regulation';
                    $this->handleCatalogue($pageCrawl, $anchorElem, $url, $type);
                }
            });
    }

    /**
     * @param PageCrawl     $pageCrawl
     * @param array<string> $meta
     * @param string        $title
     * @param string        $href
     * @param string        $uniqueId
     *
     * @return void
     */
    protected function createCatalogue(PageCrawl $pageCrawl, string $title, string $href, string $uniqueId, array $meta = []): void
    {
        $catDoc = new CatalogueDoc([
            'title' => $title,
            'source_unique_id' => $uniqueId,
            'view_url' => $href,
            'start_url' => $href,
            'primary_location_id' => '2386',
            'language_code' => 'eng',
        ]);

        $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

        CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
            'doc_meta' => $meta,
        ]);

        $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl, DOMElement $anchorElem, string $url, string $type): void
    {
        $meta = [];

        $title = trim($anchorElem->textContent);
        /** @var string $title */
        $title = preg_replace('/\n+/', ' ', $title);
        $workNumber = Str::of($url)->afterLast('/');

        $url = str_starts_with($title, 'Table of Contents')
            ? "{$url}_multi"
            : $url;

        /** @var DOMElement $uniqueId */
        $uniqueId = $pageCrawl->domCrawler->filter('.sizetext1');
        $uniqueId = $uniqueId->textContent ?? $workNumber;

        $meta['work_number'] = $workNumber;
        $meta['work_type'] = $type;

        $this->createCatalogue($pageCrawl, $title, $url, $uniqueId, $meta);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getYearLink(PageCrawl $pageCrawl): void
    {
        $yearLinks = $pageCrawl->domCrawler->filter('.search-results.statutes-regulations li.stat-reg-folder a');

        /** @var DOMElement $yearLink */
        foreach ($yearLinks as $yearLink) {
            $year = $yearLink->textContent;
            $currentYear = Carbon::now()->format('Y');

            if ($year === $currentYear) {
                if (str_contains($pageCrawl->pageUrl->url, 'civix/content/oic/oic_cur')) {
                    $link = 'https://www.bclaws.gov.bc.ca/civix/content/oic/oic_cur/' . $yearLink->getAttribute('href');

                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]));
                } elseif (str_contains($pageCrawl->pageUrl->url, 'civix/content/mo/mo')) {
                    $link = 'https://www.bclaws.gov.bc.ca/civix/content/mo/mo/' . $yearLink->getAttribute('href');

                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]));
                }
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getLatestUpdates(PageCrawl $pageCrawl): void
    {
        $latestLink = $pageCrawl->domCrawler->filter('.search-results.statutes-regulations li.stat-reg-folder:last-child a');
        $latestHref = $latestLink->link()->getUri();

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $latestHref]));
    }

    /**
     * @param DOMElement $links
     *
     * @return DocMetaDto
     */
    protected function handleDocMeta(DOMElement $links): DocMetaDto
    {
        $domCrawler = new DomCrawler($links);
        $docMetaDto = new DocMetaDto();
        /** @var DOMElement $anchorElem */
        $anchorElem = $domCrawler->filterXPath('.//a')->getNode(0);
        $url = "https://www.bclaws.gov.bc.ca{$anchorElem->getAttribute('href')}";
        $id = Str::of($url)->afterLast('/');

        $title = str_contains($url, '/oic/oic_cur')
            ? 'Order in Council  ' . $anchorElem->textContent
            : 'Ministerial Order  ' . $anchorElem->textContent;

        $docMetaDto->title = $title;
        $docMetaDto->source_unique_id = "updates_{$id}";
        $docMetaDto->source_url = $url;
        $docMetaDto->download_url = $url;
        $docMetaDto->work_type = 'notice';
        $docMetaDto->language_code = 'eng';
        $docMetaDto->primary_location = '2386';
        $docMetaDto->work_number = $id;

        return $docMetaDto;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.search-results.statutes-regulations li',
            function (DOMElement $links) use ($pageCrawl) {
                $docMetaDto = $this->handleDocMeta($links);

                $link = new UrlFrontierLink(['url' => $docMetaDto->source_url]);
                $link->_metaDto = $docMetaDto;
                $this->arachno->followLink($pageCrawl, $link, true);
            });
    }

    protected function handleCivixMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '2386');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);

        $downloadUrl = str_contains((string) $catalogueDoc->source_unique_id, 'updates_')
            ? $catalogueDoc->start_url
            : null;

        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['work_number'] ?? null);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'act');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function capturePageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'body',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#title',
            ],
            // [
            //     'type' => DomQueryType::XPATH,
            //     'query' => '//head//style',
            // ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'head link[href*="/standards"]',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#header',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '#contents',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'p[class="copyright"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div#toolBar',
            ],
        ]);

        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('a[name]') as $section) {
                /** @var DOMElement $section */
                $section->setAttribute('id', $section->getAttribute('name'));
            }

            foreach ($crawler->filter('a[name*="."]') as $section) {
                /** @var DOMElement $section */
                $nameAttr = str_replace('.', '_', $section->getAttribute('name'));
                $section->setAttribute('name', $nameAttr);
                $section->setAttribute('id', $nameAttr);
            }

            /** @var DOMElement $pageContent */
            $pageContent = $crawler->filter('#contentsscroll')->getNode(0);
            $pageContent->setAttribute('style', 'position:relative;width:100%;');

            if ($crawler->filter('[href*="/document"], [href*="/content/crbc"]')->count() > 0) {
                foreach ($crawler->filter('[href*="/document"], [href*="/content/crbc"]') as $htmlLink) {
                    /** @var DOMElement $htmlLink */
                    $htmlLink->removeAttribute('href');
                }
            }

            return $crawler;
        };
        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }

    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $extractLink = function ($nodeDTO) use ($pageCrawl) {
            $href = $nodeDTO['node']->getAttribute('href');
            $href = explode('#', $href);
            $url = $pageCrawl->pageUrl->url;

            if ($href[1] ?? false) {
                $hrefVal = $url . '#' . str_replace(['#', '.'], ['', '_'], $href[1]);

                return $hrefVal;
            }

            return $href[0];
        };
        $extractUniqueId = function ($nodeDTO) {
            return $nodeDTO['node']->getAttribute('href');
        };

        $extractLabel = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');

            if (str_contains($href, 'section')) {
                /** @var DOMElement $row */
                $row = $nodeDTO['node']->parentNode->parentNode;
                $row = collect($row->childNodes)->map(fn ($child) => $child->textContent)->join(' ');

                return trim($row);
            }

            return $nodeDTO['node']->textContent;
        };

        $matchesToc = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');

            return strtolower($nodeDTO['node']->nodeName) === 'a'
                && $href;
        };

        $criteria = function ($nodeDTO) use ($matchesToc) {
            $matches = $matchesToc($nodeDTO);

            if (!$matches) {
                return false;
            }

            $parent = $nodeDTO['node']->parentNode->parentNode;
            $domCrawler = new DomCrawler($parent);
            $domCrawler = $domCrawler->filterXPath('.//a');

            if ($domCrawler->count() === 2 && $domCrawler->getNode(0) !== $nodeDTO['node']) {
                return !$matchesToc(['node' => $domCrawler->getNode(0)]);
            }

            return $matches;
        };

        //        $determineDepth = function ($nodeDTO) {
        //            $column = $nodeDTO['node']->parentNode->getAttribute('colspan');
        //
        //            return match ($column ?? '') {
        //                '3' => 1,
        //                '2' => 2,
        //                default => 3
        //            };
        //        };
        $this->arachno->captureTocCSS(
            $pageCrawl,
            '#contents',
            $criteria,
            $extractLabel,
            $extractLink,
            $extractUniqueId,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ca-bc-laws',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    //                    new UrlFrontierLink([
                    //                        'url' => 'https://www.bclaws.gov.bc.ca/civix/content/bcgaz2/bcgaz2/?xsl=/templates/browse.xsl',
                    //                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://www.bclaws.gov.bc.ca/civix/content/oic/oic_cur/?xsl=/templates/browse.xsl',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://www.bclaws.gov.bc.ca/civix/content/mo/mo/?xsl=/templates/browse.xsl',
                    ]),
                ],

                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.bclaws.gov.bc.ca/civix/content/complete/statreg/?xsl=/templates/browse.xsl',
                    ]),
                ],
            ],
        ];
    }
}
