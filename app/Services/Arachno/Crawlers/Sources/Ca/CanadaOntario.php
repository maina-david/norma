<?php

namespace App\Services\Arachno\Crawlers\Sources\Ca;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: ca-ontario
 * title: ontario.ca
 * url: https://www.ontario.ca
 */
class CanadaOntario extends AbstractCrawlerConfig
{
    /**
     * @return string
     */
    protected function handleDate(): string
    {
        $yesterday = Carbon::now()->subDays(2)->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        return "https://www.ontario.ca/search/ontario-gazette?date_from={$yesterday}&date_to={$today}";
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ca-ontario',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://www.ontario.ca/laws?filtertype=Statute%2CRegulation']),
                ],
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => $this->handleDate()]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\/search\/ontario-gazette\?/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\/document\/ontario-gazette-volume/')) {
            $this->getPdfLink($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->matchCSS($pageCrawl, '#left')) {
            $this->handleMeta($pageCrawl);
            $this->getPageContent($pageCrawl);
            $this->handleToc($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.views-row.gazette-result--item-page article',
            function (DOMElement $elements) use ($pageCrawl) {
                $domCrawler = new DomCrawler($elements);
                $docMetaDto = new DocMetaDto();

                /** @var DOMElement $anchor */
                $anchor = $domCrawler->filterXPath('.//h2/a')->getNode(0);
                $title = $anchor->textContent;

                $url = "https://www.ontario.ca{$anchor->getAttribute('href')}";
                /** @var array<string> $match */
                $match = preg_split('/issue-\d+-/', $url);
                $effDate = explode('-', $match[1]);
                $effDate = implode(' ', $effDate);

                /** @var DOMElement|null $workDate */
                $workDate = $domCrawler->filterXPath('.//div[@class="gazette--dates-wrapper"]/small/div')->getNode(0);
                $workDate = trim($workDate?->textContent ?? '');

                try {
                    $docMetaDto->work_date = Carbon::parse($workDate);
                    $docMetaDto->effective_date = Carbon::parse($effDate);
                } catch (InvalidFormatException) {
                }

                $docMetaDto->title = $title;
                $docMetaDto->source_url = $url;
                $docMetaDto->primary_location = '2393';
                $docMetaDto->language_code = 'eng';
                $docMetaDto->work_type = 'gazette';

                $link = new UrlFrontierLink(['url' => $url]);
                $link->_metaDto = $docMetaDto;
                $this->arachno->followLink($pageCrawl, $link, true);
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getPdfLink(PageCrawl $pageCrawl): void
    {
        $pdfLink = $pageCrawl->domCrawler->filter('.small-12.columns a.button.button--primary')->link()->getUri();

        $uniqueId = Str::of($pdfLink)->afterLast('/')->before('.pdf');

        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', "updates_{$uniqueId}");
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pdfLink);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $pdfLink]), true);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '#search_results td', function (DOMElement $element) use ($pageCrawl) {
            $domCrawler = new DomCrawler($element);
            $meta = [];
            /** @var DOMElement $urlElem */
            $urlElem = $domCrawler->filterXPath('.//a')->getNode(0);
            $url = "https://www.ontario.ca{$urlElem->getAttribute('href')}";

            //            $title = $domCrawler->filterXPath('.//div[@class="reg-act"]')->getNode(0);
            //            $title = $title->textContent ?? $urlElem->textContent;
            $title = $urlElem->textContent;

            /** @var DOMElement|null $workDate */
            $workDate = $domCrawler->filterXPath('.//span[@class="time"]')->getNode(0);
            $workDate = trim($workDate?->textContent ?? '');

            $workNumber = Str::of($url)->afterLast('/');
            $alternativeId = Str::of($url)->after('laws/');
            $urlText = trim($urlElem->textContent);
            $uniqueId = str_contains($urlText, ':') ? explode(':', $urlText)[0] : $alternativeId;

            $meta['work_number'] = $workNumber;
            $meta['work_date'] = $workDate;

            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'source_unique_id' => $uniqueId,
                'view_url' => $url,
                'start_url' => $url,
                'primary_location_id' => '2393',
                'language_code' => 'eng',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
            if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
                $this->handleNextPage($pageCrawl);
            }
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleNextPage(PageCrawl $pageCrawl): void
    {
        if ($pageCrawl->domCrawler->filter('.cq-load-more.right a')->getNode(0)) {
            $href = $pageCrawl->domCrawler->filter('.cq-load-more.right a')->link()->getUri();

            $this->arachno->followLink(
                $pageCrawl,
                new UrlFrontierLink(['url' => $href, 'anchor_text' => 'Next']),
                true
            );
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
        $catalogueDoc->load('docMeta');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '2393');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['work_number'] ?? null);

        try {
            $workDate = $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null;
            $workDate = Carbon::parse($workDate);

            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $workDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
        } catch (InvalidFormatException) {
        }

        /** @var string $viewUrl */
        $viewUrl = $catalogueDoc->view_url;
        $workType = Str::of($viewUrl)->after('laws/')->before('/') == 'regulation'
            ? 'regulation'
            : 'statutory_instrument';
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    protected function getPageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                //                'query' => '.WordSection1',
                'query' => '#left',
            ],
        ]);

        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head/style',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"]',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.footnoteLeft-e.leg-history collapsed',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.ConsolidationPeriod-e',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.MsoNormal a[href*="/fr/lois"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.ConsolidationPeriod',
            ],
            //            [
            //                'type' => DomQueryType::CSS,
            //                'query' => '.footnoteLeft.leg-history.collapsed',
            //            ],
        ]);

        $preStore = function (DomCrawler $crawler) {
            if ($crawler->filter('a[name]')->count() > 0) {
                foreach ($crawler->filter('a[name]') as $section) {
                    /** @var DOMElement $section */
                    $section->setAttribute('id', $section->getAttribute('name'));
                }
            }

            //            /** @var DOMElement $tocLink */
            //            foreach ($crawler->filter('.MsoNormalTable a[href*="#"]') as $tocLink) {
            //                /** @var DOMElement $tocLink */
            //                $title = strtolower($tocLink->getAttribute('title'));
            //                $html = $tocLink->ownerDocument->saveHTML($tocLink);
            //                if (str_contains($title, 'part')) {
            //                    $element = $tocLink->ownerDocument->createElement('h2');
            //                } elseif (str_contains($title, 'section')) {
            //                    $element = $tocLink->ownerDocument->createElement('h4');
            //                } else {
            //                    $element = $tocLink->ownerDocument->createElement('h3');
            //                }
            //
            //                $tocLink->parentNode->insertBefore($element, $tocLink);
            //                $element->appendChild($tocLink);
            //            }

            /** @var int $tocHeadCount */
            $tocHeadCount = $crawler->filter('.toc-e,.toc')->count();

            if ($tocHeadCount && $tocHeadCount > 0) {
                /** @var DOMElement $tocHead */
                $tocHead = $crawler->filter('.toc-e,.toc')->getNode($tocHeadCount - 1);
                $tocHead->nextElementSibling?->remove();
                $tocHead->remove();
            } else {
                /** @var DOMElement|null $tocHead */
                $tocHead = $crawler->filter('.toc-e,.toc')->getNode($tocHeadCount - 1);
                $tocHead?->nextElementSibling?->remove();
                $tocHead?->remove();
            }

            /** @var DOMElement $frenchLink */
            foreach ($crawler->filter('a[href*="french"]') as $frenchLink) {
                if (str_contains($frenchLink->textContent, 'Français')) {
                    $frenchLink->remove();
                }
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }

    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $extractLink = function ($nodeDTO) {
            return $nodeDTO['node']->getAttribute('href');
        };

        $extractLabel = function ($nodeDTO) {
            $domCrawler = new DomCrawler($nodeDTO['node']);
            /** @var DOMElement $row */
            $row = $domCrawler->closest('tr')?->getNode(0);

            return trim($row->textContent);
        };

        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');

            return Str::of($href)->after('#');
        };

        $determineDepth = function ($nodeDTO) {
            $type = $nodeDTO['node']->getAttribute('title');
            $type = strtolower($type);

            return match (explode(' ', $type)[0]) {
                'part' => 1,
                //                'section' => 3,
                default => 2
            };
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            '.MsoNormalTable',
            null,
            $extractLabel,
            $extractLink,
            $extractUniqueId,
            $determineDepth
        );
    }
}
