<?php

namespace App\Services\Arachno\Crawlers\Sources\Uk;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\TocItemDraft;
use DOMElement;
use Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Throwable;

/**
 * @codeCoverageIgnore
 * slug: uk-legislation-gov-uk
 * title: Legislation.gov.uk
 * url: https://www.legislation.gov.uk/
 */
class LegislationGovUk extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if (
            ($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl))
            && $this->arachno->matchUrl($pageCrawl, '/\/data\.feed/')
        ) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\/data\.akn/')) {
                $doc = $this->handleMeta($pageCrawl);
                $this->handleCoverPage($pageCrawl, $doc);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
            if ($this->arachno->matchCSS($pageCrawl, '#content #viewLegContents')) {
                $this->capturePageContent($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsWorkChangesDiscovery($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/changes\/applied\/affected\//')) {
            $this->handleChangesApplied($pageCrawl);
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/data\.feed/')) {
            $this->handleForUpdatesIndex($pageCrawl);
        }

        if ($this->arachno->crawlIsSearch($pageCrawl)) {
            $this->handleSearch($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[local-name()="entry"]', function (DOMElement $entry) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($entry);
            $id = $entryCrawler->filterXPath('//*[local-name()="id"]')->getNode(0)?->textContent ?? '';
            $id = str_replace('http://www.legislation.gov.uk/id', '', $id);
            $title = $entryCrawler->filterXPath('//*[local-name()="title"]')->getNode(0)?->textContent ?? '';
            /** @var DOMElement|null */
            $tocLink = $entryCrawler->filterXPath('//*[local-name()="link" and @rel="http://purl.org/dc/terms/tableOfContents" and not(@hreflang)]')->getNode(0);
            $url = $tocLink?->getAttribute('href') ?? '';
            $url = str_replace('http://', 'https://', $url);
            if (!$url || str_contains(strtolower($title), 'repealed')) {
                return;
            }
            $summary = trim($entryCrawler->filterXPath('//*[local-name()="summary"]')->getNode(0)?->textContent ?? '');
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => str_contains($url, 'contents/made') || str_contains($url, '/apni') ? $url . '/data.akn' : $url . '/made/data.akn',
                'view_url' => $url,
                'source_unique_id' => (string) $id,
                'language_code' => 'eng',
                'summary' => $summary,
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            $juri = (string) Str::of($id)->after('/')->before('/');
            $juriName = $this->getNameByJuri($juri);
            if ($catalogueDoc->created_at > now()->subHour() && $juriName) {
                $this->arachno->addCategoriesToCatalogueDoc($catalogueDoc, [$juriName]);
            }
        });

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->followNextFeedLink($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followNextFeedLink(PageCrawl $pageCrawl): void
    {
        //        $hasFailed = $pageCrawl->domCrawler->filterXPath('//h1[contains(text(), "Internal Error")]')->getNode(0);

        /** @var DOMElement|null $nextLink */
        $nextLink = $pageCrawl->domCrawler->filterXPath('//*[local-name()="link" and @rel="next" and @type="application/atom+xml"]')->getNode(0);
        if ($nextLink) {
            $href = $nextLink->getAttribute('href');
            $href = str_replace(['http://', '&amp;'], ['https://', '&'], $href);
            // Page 9 for EUR was returning an error so will exclude when needed from the followed Links
            //            $href = str_contains($href, '/eur/data.feed?results-count=200&page=22') ? Str::of($href)->replace('page=22', 'page=23') : $href;

            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $href, 'anchor_text' => 'Next']));

            return;
        }

        $href = $pageCrawl->pageUrl->url;
        $pageNum = Str::of($href)->after('&page=')->toString();
        $pageNum = (int) $pageNum;
        if ($pageNum <= 300) {
            $pageNum = $pageNum++;
            $href = Str::of($href)->before('&page=');
            $href = "{$href}&page={$pageNum}";
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $href, 'anchor_text' => 'Next']));
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleChangesApplied(PageCrawl $pageCrawl): void
    {
        $uniqueIds = [];
        $this->arachno->eachX($pageCrawl, '//*[local-name()="entry"]', function (DOMElement $entry) use (&$uniqueIds) {
            $entryCrawler = new DomCrawler($entry);
            $id = $entryCrawler->filterXPath('//*[local-name()="Effect"]/@AffectedURI')->getNode(0)?->textContent ?? '';
            $id = str_replace('http://www.legislation.gov.uk/id', '', $id);
            $uniqueIds[$id] = true;
        });
        $uniqueIds = array_keys($uniqueIds);
        $this->arachno->fetchChangedDocs($uniqueIds, $pageCrawl->pageUrl->crawl?->crawler?->source_id ?? 0);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleForUpdatesIndex(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[local-name()="entry"]', function (DOMElement $entry) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($entry);
            $href = $entryCrawler->filterXPath('//*[local-name()="link" and @type="application/pdf"]/@href')->getNode(0)?->textContent ?? '';
            if (!$href) {
                $href = $entryCrawler->filterXPath('//*[local-name()="link" and @rel="self"]/@href')->getNode(0)?->textContent ?? '';
            }
            // we're including the .pdf in the ID here, so it's distinct from docs that are not used for updates, where we want the same doc, but not as PDF
            $id = str_replace('http://www.legislation.gov.uk', '', $href);
            $href = str_replace('http://', 'https://', $href);
            $title = $entryCrawler->filterXPath('//*[local-name()="title"]')->getNode(0)?->textContent ?? '';
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = $id;
            $docMeta->title = $title;
            $docMeta->language_code = 'eng';
            $docMeta->source_url = $entryCrawler->filterXPath('//*[local-name()="id"]')->getNode(0)?->textContent ?? null;
            $docMeta->download_url = $href;
            $juri = $this->getJuriFromId($id);
            $jurisdictionMapping = $this->getJurisdictions()[$juri] ?? [];
            if (empty($jurisdictionMapping)) {
                // for updates we're fetching the full list of changes for ALL jurisdictions.... but we only want the ones in jurisdictionMapping
                return;
            }
            $docMeta->primary_location = (string) ($jurisdictionMapping['location_id'] ?? '6');

            $date = $entryCrawler->filterXPath('//*[local-name()="CreationDate"]/@Date')->getNode(0)?->textContent ?? '';
            if ($date) {
                try {
                    $docMeta->work_date = Carbon::parse($date);
                } catch (Throwable $th) {
                }
            }
            $docMeta->summary = $entryCrawler->filterXPath('//*[local-name()="summary"]')->getNode(0)?->textContent ?? null;
            $keywords = [];
            foreach ($entryCrawler->filterXPath('//*[local-name()="category"]') as $cat) {
                /** @var DOMElement $cat */
                $keywords[] = $cat->getAttribute('term');
            }
            if (!empty($keywords)) {
                $docMeta->keywords = $keywords;
            }

            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleSearch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/\/data\.feed/')) {
            $this->followNextFeedLink($pageCrawl);

            $this->arachno->eachX($pageCrawl, '//*[local-name()="entry"]', function (DOMElement $entry) use ($pageCrawl) {
                $entryCrawler = new DomCrawler($entry);
                $link = $entryCrawler->filterXPath('//*[local-name()="link" and @rel="self"]/@href')->getNode(0)?->textContent ?? '';
                $link = str_replace(['http://', '/id/'], ['https://', '/'], $link);
                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]));
            });
        } else {
            if ($this->arachno->matchCSS($pageCrawl, '#viewLegContents .downloadPdfVersion a.pdfLink')) {
                $this->arachno->followLinksCSS($pageCrawl, '#viewLegContents .downloadPdfVersion a.pdfLink');
            // $this->arachno->indexForSearch($pageCrawl, new DomQuery(['query' => '#pageTitle', 'type' => DomQueryType::CSS]));
            } else {
                $this->arachno->indexForSearch(
                    $pageCrawl,
                    contentQuery: new DomQuery(['query' => '#viewLegContents', 'type' => DomQueryType::CSS]),
                    primaryLocationId: 6,
                    languageCode: 'eng',
                );
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        $url = $pageCrawl->getFinalRedirectedUrl();
        $id = (string) Str::of($url)
            ->after('legislation.gov.uk')
            ->before('/contents');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'language_code', '//*[local-name()="FRBRExpression"]//*[local-name()="FRBRlanguage"]/@language');
        $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'title', '//dc:title');

        $juri = $this->getJuriFromId($id);
        $jurisdictionMapping = $this->getJurisdictions()[$juri] ?? [];
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', (string) ($jurisdictionMapping['location_id'] ?? '6'));
        if ($workType = $jurisdictionMapping['type']) {
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) $workType);
        }
        $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'work_date', '//*[local-name()="FRBRWork"]//*[local-name()="FRBRdate"]/@date');
        $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'work_number', '//*[local-name()="FRBRWork"]//*[local-name()="FRBRnumber"]/@value');
        $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'summary', '//dc:description');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', str_replace('/data.akn', '', $url));
        $this->arachno->eachX($pageCrawl, '//*[local-name()="classification"]//*[local-name()="keyword"]/@showAs', fn ($item) => $this->arachno->setDocMetaProperty($pageCrawl, 'keywords', [$item->textContent]));

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param Doc       $doc
     *
     * @return void
     */
    protected function handleCoverPage(PageCrawl $pageCrawl, Doc $doc): void
    {
        $tocCrawler = $pageCrawl->domCrawler->filterXPath('//*[local-name()="coverPage"]//*[local-name()="toc"]//*[local-name()="tocItem"]');
        if ($tocCrawler->count() === 0) {
            /** @var DOMElement|null */
            $pdfLink = $pageCrawl->domCrawler->filterXPath('//atom:link[@type="application/pdf"]')->getNode(0);
            if ($href = $pdfLink?->getAttribute('href')) {
                $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => 'Pdf']);
                $l->_metaDto = new DocMetaDto(['source_unique_id' => $doc->source_unique_id]);
                $this->arachno->followLink($pageCrawl, $l);
            }
        } elseif ($tocCrawler->count() > 200) {
            $this->captureTocMultiplePages($pageCrawl, $doc);
        } else {
            $this->captureTocOnePage($pageCrawl, $doc);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param Doc       $doc
     *
     * @return void
     */
    protected function captureTocOnePage(PageCrawl $pageCrawl, Doc $doc): void
    {
        $this->captureTocUk($pageCrawl, $doc, false);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param Doc       $doc
     *
     * @return void
     */
    protected function captureTocMultiplePages(PageCrawl $pageCrawl, Doc $doc): void
    {
        $this->captureTocUk($pageCrawl, $doc, true);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param Doc       $doc
     * @param bool      $multiple
     *
     * @return void
     */
    protected function captureTocUk(PageCrawl $pageCrawl, Doc $doc, bool $multiple = false): void
    {
        $url = $pageCrawl->pageUrl->url;
        $itemLink = (string) Str::of($url)->replace('http://', 'https://')->replace('/made', '');
        $pageCrawl->pageUrl->url = $itemLink;
        $tocCrawler = $pageCrawl->domCrawler->filterXPath('//*[local-name()="coverPage"]//*[local-name()="toc"]//*[local-name()="tocItem"]');
        $href = 'https://www.legislation.gov.uk' . $doc->source_unique_id;
        $items = [];
        $rootLevel = 1;
        foreach ($tocCrawler as $item) {
            /** @var DOMElement $item */
            $itemId = $item->getAttribute('href');
            $itemId = str_replace(['http://www.legislation.gov.uk', $doc->source_unique_id], '', $itemId);
            $eurLinkId = Str::of($itemId)->replace(['/made', '/enacted', '/adopted'], '');
            $itemId = (string) Str::of($itemId)
                ->trim('/')
                ->replace(['/made', '/enacted'], '')
                ->replace('/', '-');
            $level = (int) $item->getAttribute('level');
            $itemHref = str_replace('http://', 'https://', $item->getAttribute('href'));
            if ($multiple && $level === $rootLevel) {
                if ($itemHref === '') {
                    $rootLevel++;
                } else {
                    $href = $itemHref;
                }
            }

            $class = $item->getAttribute('class');
            $numHeading = [];
            $itemCrawler = new DomCrawler($item);
            foreach ($itemCrawler->filterXpath('//*[local-name()="inline"]') as $inline) {
                $numHeading[] = $inline->textContent;
            }
            $label = $class === 'schedules' ? 'Schedules' : implode(' ', $numHeading);
            if (!$label) {
                $label = match ($class) {
                    'schedules' => 'Schedules',
                    'title' => $item->textContent,
                    default => '',
                };
            }
            $itemId = str_contains($itemId, 'schedule-crossheading-maximumnoiselevels-paragraph') ?
                Str::of($itemId)->replace('crossheading-maximumnoiselevels', 'part-1') :
                Str::of($itemId)->replace('crossheading-noiseevaluationmethodfornoisecertificationofmicrolightaeroplanes', 'part-2');
            $itemId = Str::of($itemId)->replace('-adopted', '');
            $isEurLink = str_contains($href, 'www.legislation.gov.uk/eur');
            $items[] = new TocItemDraft([
                'href' => $itemHref && (bool) $isEurLink === false ? ($href . '#' . $itemId) : ($isEurLink ? $href . $eurLinkId : ''),
                'label' => $label,
                'level' => $level,
                'source_unique_id' => $itemId,
            ]);
        }
        $this->arachno->captureTocFromGenerator($pageCrawl, $this->toGenerator($items));
    }

    /**
     * @param array<TocItemDraft> $items
     *
     * @return Generator<TocItemDraft>
     */
    public function toGenerator(array $items): Generator
    {
        foreach ($items as $item) {
            yield $item;
        }
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
                'query' => '#viewLegContents',
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
                'query' => '.part > h2, .chapter > h2',
            ],
        ]);

        $preStore = function (DomCrawler $domCrawler) {
            foreach ($domCrawler->filter('p a') as $el) {
                /** @var DOMElement $el */
                $el->removeAttribute('href');
            }

            foreach ($domCrawler->filter('a[href*="#"]') as $hrefEl) {
                /** @var DOMElement $hrefEl */
                $hrefEl->removeAttribute('href');
            }

            return $domCrawler;
        };
        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'uk-legislation-gov-uk',
            'start_urls' => $this->getUkStartUrls(),
            'throttle_requests' => 100,
        ];
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getUkStartUrls(): array
    {
        $juris = array_keys($this->getJurisdictions());
        $fullOut = [];
        $newOut = [];
        $changedOut = [];
        $updatesOut = [];

        foreach ($juris as $juri) {
            $fullOut[] = new UrlFrontierLink([
                'url' => "https://www.legislation.gov.uk/{$juri}/data.feed?page=1&results-count=500",
            ]);
            $newOut[] = new UrlFrontierLink([
                // we'll limit the new catalogue items to the last 50, as we'll run this daily, and there are unlikely to be more than 50 in one day per type
                'url' => "https://www.legislation.gov.uk/new/{$juri}/data.feed?results-count=50",
            ]);
            $changedOut[] = new UrlFrontierLink([
                'url' => "https://www.legislation.gov.uk/changes/applied/affected/{$juri}/affecting/all/" . now()->format('Y') . '/data.feed?results-count=1000&sort=affecting-year-number&order=descending',
            ]);
            $updatesOut[] = new UrlFrontierLink([
                'url' => 'https://www.legislation.gov.uk/new/data.feed?sort=published&results-count=75',
            ]);
        }
        // return [['url' => 'https://www.legislation.gov.uk/ukpga/data.feed?sort=published&page=1']];

        return [
            'type_' . CrawlType::FULL_CATALOGUE->value => $fullOut,
            'type_' . CrawlType::NEW_CATALOGUE_WORK_DISCOVERY->value => $newOut,
            'type_' . CrawlType::WORK_CHANGES_DISCOVERY->value => $changedOut,
            'type_' . CrawlType::FOR_UPDATES->value => $updatesOut,
            'type_' . CrawlType::SEARCH->value => $fullOut,
        ];
    }

    /**
     * @param string $juri
     *
     * @return string
     */
    protected function getNameByJuri(string $juri): string
    {
        return $this->getJurisdictions()[$juri]['name'] ?? '';
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function getJuriFromId(string $id): string
    {
        return (string) Str::of($id)->after('/')->before('/');
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, array<string, mixed>>
     */
    public function getJurisdictions(): array
    {
        return [
            'anaw' => [
                'location_id' => 8, // wales
                'type' => 'act',
                'language_code' => 'eng',
                'name' => 'Acts of the National Assembly for Wales',
            ],
            'apni' => [
                'location_id' => 42486, // Northern Ireland
                'type' => 'act',
                'language_code' => 'eng',
            ],
            'asp' => [
                'location_id' => 7, // scotland
                'type' => 'act',
                'language_code' => 'eng',
                'name' => 'Acts of the Scottish Parliament',
            ],
            // 'mnia' => [
            //     'location_id' => 42486, // Northern Ireland
            //     'type' => 'measures',
            //     'language_code' => 'eng',
            // ],
            'mwa' => [
                'location_id' => 8,
                'type' => 'act',
                'language_code' => 'eng',
                'name' => 'Measures of the National Assembly',
            ],
            'nia' => [
                'location_id' => 42486,
                'type' => 'act',
                'language_code' => 'eng',
                'name' => 'Acts of the Northern Ireland Assembly',
            ],
            'nisi' => [
                'location_id' => 42486,
                'type' => 'statutory_instrument',
                'language_code' => 'eng',
                'name' => 'Northern Ireland Orders in Council',
            ],
            'nisr' => [
                'location_id' => 42486,
                'type' => 'rule',
                'language_code' => 'eng',
                'name' => 'Northern Ireland Statutory Rules',
            ],
            // 'nisro' => [
            //     'location_id' => 42486,
            //     'type' => 'rule',
            //     'language_code' => 'eng',
            // ],
            'ssi' => [
                'location_id' => 7,
                'type' => 'statutory_instrument',
                'language_code' => 'eng',
                'name' => 'Scottish Statutory Instruments',
            ],
            // 'ukci' => [
            //     'location_id' => 6,
            //     'type' => 'statutory_instrument',
            //     'language_code' => 'eng',
            // ],
            // 'ukcm' => [
            //     'location_id' => 6,
            //     'type' => 'measures',
            //     'language_code' => 'eng',
            // ],
            'ukla' => [
                'location_id' => 6,
                'type' => 'act',
                'language_code' => 'eng',
                'name' => 'UK Local Acts',
            ],
            'ukmo' => [
                'location_id' => 6,
                'type' => 'order',
                'language_code' => 'eng',
                'name' => 'UK Ministerial Orders',
            ],
            'ukpga' => [
                'location_id' => 6,
                'type' => 'act',
                'language_code' => 'eng',
                'name' => 'UK Public General Acts',
            ],
            'uksi' => [
                'location_id' => 6,
                'type' => 'statutory_instrument',
                'language_code' => 'eng',
                'name' => 'UK Statutory Instruments',
            ],
            // 'uksro' => [
            //     'location_id' => 6,
            //     'type' => 'rule',
            //     'language_code' => 'eng',
            // ],
            'wsi' => [
                'location_id' => 8,
                'type' => 'statutory_instrument',
                'language_code' => 'eng',
                'name' => 'Wales Statutory Instruments',
            ],
            'eur' => [
                'location_id' => 9,
                'type' => 'regulation',
                'language_code' => 'eng',
            ],
        ];
    }
}
