<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\TocItemDraft;
use App\Services\Arachno\Support\DomClosest;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use DOMNode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 *
 * slug: us-ecode-360-ordinances
 * title: USA Ecode360 Ordinances
 * url: https://ecode360.com
 */
class Ecode360Ordinances extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchCSS($pageCrawl, 'div#contentContainer')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchCSS($pageCrawl, 'body#lawsPage')) {
                $this->handleLawsPage($pageCrawl);
                // $this->getDispositionLinks($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            if ($this->arachno->matchUrl($pageCrawl, '/print\/[A-Z0-9]+\?guid=/')) {
                $this->handleCapture($pageCrawl);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        $pageCrawl->setProxySettings([
            'provider' => 'scraping_bee',
            'options' => ['render_js' => false],
        ]);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $url = $pageCrawl->pageUrl->url;
        $href = $pageCrawl->domCrawler->filter('a#printButton')->link()->getUri();
        $code = (string) Str::of($href)->after('guid=');
        $locations = $this->getEcode360LocationsAndTitles()[$code]['location_id'] ?? null;
        $title = $this->getEcode360LocationsAndTitles()[$code]['title'] ?? null;
        $catDoc = new CatalogueDoc([
            'title' => trim($title),
            'start_url' => $href,
            'view_url' => $url,
            'source_unique_id' => $code,
            'language_code' => 'eng',
            'primary_location_id' => $locations,
        ]);

        $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catalogueDoc->primary_location_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'code');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'start_url', $catalogueDoc->start_url);

        return $doc;
    }

    protected function handleLawsPage(PageCrawl $pageCrawl): void
    {
        // @codeCoverageIgnoreStart
        $dom = $pageCrawl->domCrawler;
        $jurisdiction = $dom->filter('head title')->getNode(0)?->textContent ?? '';
        $jurisdiction = str_replace(' New Laws', '', $jurisdiction);
        $baseUrl = $pageCrawl->getFinalRedirectedUrl();
        $prefix = str_replace('https://ecode360.com/', '', $baseUrl);
        $prefix = Str::after($prefix, 'laws/');
        $locations = $this->getEcode360LocationsAndTitles()[$prefix];
        foreach ($dom->filter('table#newLawsTable tr .titleLawCell a') as $link) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $href = (string) app(LinkToUri::class)->convert($href, $baseUrl);
            $uniqueId = Str::of($href)->after('com/')->before('.pdf');
            $docTitle = trim($link->textContent);
            $title = implode(' - ', [$jurisdiction, $docTitle]);
            $title = Str::of($title)->replace('pdf', '');
            $docMeta = new DocMetaDto();
            // $titleNoSpaces = preg_replace(['/\s+/', '/,/'], '', $title);
            $docMeta->source_unique_id = $uniqueId;
            $docMeta->title = $title;
            $docMeta->work_type = 'ordinance';
            $docMeta->language_code = 'eng';
            $closest = app(DomClosest::class);
            /** @var DOMNode $link */
            /** @var Crawler $tr */
            $tr = $closest->closest($link, 'tr');

            $hasDate = $tr->filter('.adoptedLawCell')->count() > 0;
            try {
                if ($hasDate) {
                    $date = $tr->filter('.adoptedLawCell')->getNode(0)?->textContent ?? null;
                    $docMeta->work_date = $date ? Carbon::createFromFormat('Y-m-d', substr(trim($date), 0, 10)) : null;
                }
            } catch (InvalidFormatException $th) {
            }

            $summary = $tr->filter('.subjectLawCell')->getNode(0)?->textContent ?? null;
            $affects = $tr->filter('.dispositionsLawCell')->getNode(0)?->textContent ?? null;
            $summary = ($summary ?? '') . ($affects ? ' Affects: ' . $affects : '');
            if ($summary) {
                $docMeta->summary = $summary;
            }
            $docMeta->work_number = $docTitle;
            $docMeta->primary_location = (string) ($locations['location_id'] ?? '');
            $docMeta->source_url = $href;
            $docMeta->download_url = $href;

            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => $title]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
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
                'query' => 'div#content',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'html body h1',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"/css") and @rel="stylesheet"]',
            ],
        ]);
        $preStore = function (Crawler $crawler) use ($pageCrawl) {
            foreach ($crawler->filter('a[class="titleLink"]') as $anchor) {
                $closest = app(DomClosest::class);
                /** @var DOMNode $anchor */
                /** @var Crawler $parentNode */
                $parentNode = $closest->closest($anchor, 'h2, h4');
                $parentClass = $parentNode->getNode(0);
                if ($parentClass !== null) {
                    /** @var DOMElement $parentClass */
                    $tocClass = $parentClass->getAttribute('class');
                    if ($tocClass === 'title articleTitle' && !str_starts_with(strtolower(trim($parentClass->textContent)), 'division')) {
                        $parentClass->setAttribute('class', 'title partTitle');
                    }
                }
            }
            $toc = [];
            $prevLevel = 0;
            foreach ($crawler->filter('a[class="titleLink"]') as $anchor) {
                $closest = app(DomClosest::class);
                /** @var DOMNode $anchor */
                /** @var Crawler $parentNode */
                $parentNode = $closest->closest($anchor, 'h2, h4');
                $parentClass = $parentNode->getNode(0);
                if ($parentClass !== null) {
                    /** @var DOMElement $parentClass */
                    $tocClass = $parentClass->getAttribute('class');

                    $hierarchies = [
                        'divisionTitle' => 1,
                        'chapterTitle' => 2,
                        'partTitle' => 3,
                        'articleTitle' => 4,
                        'sectionTitle' => 5,
                    ];

                    $level = 0;
                    $resetLevel = 0;

                    if ($tocClass) {
                        // Use a regular expression to match the class name
                        preg_match('/(divisionTitle|partTitle|chapterTitle|articleTitle|sectionTitle)/', $tocClass, $matches);
                        if ($matches) {
                            // Determine the level based on the matched class name
                            $level = $hierarchies[$matches[0]] ?? 0;

                            // Adjust the level based on the previous hierarchy
                            if ($prevLevel > 0 && $level > 0 && $prevLevel < $level) {
                                $level = $prevLevel + 1;
                            }
                            if ($matches[0] === 'sectionTitle') {
                                $level = $prevLevel + 1;
                                $resetLevel = $level - 1;
                            }
                            $prevLevel = $matches[0] === 'sectionTitle' ? $resetLevel : $level;
                        }
                    }
                    $url = $pageCrawl->pageUrl->url;
                    /** @var DOMElement $anchor */
                    $href = $anchor->getAttribute('href');
                    $tocId = Str::of($href)->replace('#', '');
                    $label = $anchor->textContent ?? '';
                    $anchor->setAttribute('id', $tocId);
                    $toc[] = new TocItemDraft([
                        'source_url' => $href,
                        'label' => trim($label),
                        'source_unique_id' => $href,
                        'href' => $href,
                        'level' => $level,
                    ]);
                }
            }
            $this->arachno->captureTocFromDraftArray($pageCrawl, $toc);

            return $crawler;
        };
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            preStoreCallable: $preStore,
        );
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-ecode-360-ordinances',
            'throttle_requests' => 500,
            'start_urls' => $this->getEcode360StartUrls(),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string,array<mixed>>
     */
    public function getEcode360StartUrls(): array
    {
        $arr = [
            'https://ecode360.com/laws/FO3807',
            'https://ecode360.com/laws/NE0870',
            'https://ecode360.com/laws/BA2043',
            'https://ecode360.com/laws/CH2673',
            'https://ecode360.com/laws/MA2384',
            'https://ecode360.com/laws/MO2400',
            'https://ecode360.com/laws/KA6368',
            'https://ecode360.com/laws/SU0867',
            'https://ecode360.com/laws/IS0324',
            'https://ecode360.com/laws/EA2319',
            'https://ecode360.com/laws/HA1391',
            'https://ecode360.com/laws/MA2997',
            'https://ecode360.com/laws/MI1063',
            'https://ecode360.com/laws/UP5026',
            'https://ecode360.com/laws/VE5027',
            'https://ecode360.com/laws/HU4937',
            'https://ecode360.com/laws/LA4953',
            'https://ecode360.com/laws/MI4968',
            'https://ecode360.com/laws/SA4999',
            'https://ecode360.com/laws/SA5006',
            'https://ecode360.com/laws/GL4929',
            'https://ecode360.com/laws/RE4995',
            'https://ecode360.com/laws/SA5008',
            'https://ecode360.com/laws/PL4987',
            'https://ecode360.com/laws/RO4998',
            'https://ecode360.com/laws/PA4981',
            'https://ecode360.com/laws/SU5020',
            'https://ecode360.com/laws/SA5009',
            'https://ecode360.com/laws/CL2905',
            'https://ecode360.com/laws/BO3867',
            'https://ecode360.com/laws/CL0218',
            'https://ecode360.com/laws/MA0608',
            'https://ecode360.com/laws/MO4033',
            'https://ecode360.com/laws/MA0373',
            'https://ecode360.com/laws/ME1460',
            'https://ecode360.com/laws/ES1525',
            'https://ecode360.com/laws/MO0769',
            'https://ecode360.com/laws/ME2189',
            'https://ecode360.com/laws/WA0473',
            'https://ecode360.com/laws/HU2724',
            'https://ecode360.com/laws/ED0532',
            'https://ecode360.com/laws/EN4071',
            'https://ecode360.com/laws/MO0747',
            'https://ecode360.com/laws/NE4043',
            'https://ecode360.com/laws/PA1376',
            'https://ecode360.com/laws/PA0882',
            'https://ecode360.com/laws/RE2655',
            'https://ecode360.com/laws/RI1200',
            'https://ecode360.com/laws/WA1959',
            'https://ecode360.com/laws/WE2734',
            'https://ecode360.com/laws/WO0158',
            'https://ecode360.com/laws/JE0687',
            'https://ecode360.com/laws/LA1674',
            'https://ecode360.com/laws/AL4161',
            'https://ecode360.com/laws/DE1994',
            'https://ecode360.com/laws/AL1955',
            'https://ecode360.com/laws/NE1450',
            'https://ecode360.com/laws/HA2892',
            'https://ecode360.com/laws/AN1471',
            'https://ecode360.com/laws/BE3069',
            'https://ecode360.com/laws/WE0813',
            'https://ecode360.com/laws/DE3083',
            // 'https://ecode360.com/laws/UP5026',
            'https://ecode360.com/laws/NA4976',
            'https://ecode360.com/laws/MA5043',
            'https://ecode360.com/laws/WE5031',
            'https://ecode360.com/laws/HY0759',
        ];
        $codeLinks = [
            'https://ecode360.com/FO3807',
            'https://ecode360.com/NE0870',
            'https://ecode360.com/BA2043',
            'https://ecode360.com/CH2673',
            'https://ecode360.com/MA2384',
            'https://ecode360.com/MO2400',
            'https://ecode360.com/KA6368',
            'https://ecode360.com/SU0867',
            'https://ecode360.com/IS0324',
            'https://ecode360.com/EA2319',
            'https://ecode360.com/HA1391',
            'https://ecode360.com/MA2997',
            'https://ecode360.com/MI1063',
        ];
        $out = [];
        foreach ($arr as $url) {
            $out[] = new UrlFrontierLink(['url' => $url]);
        }

        $codeArr = [];
        foreach ($codeLinks as $url) {
            $codeArr[] = new UrlFrontierLink(['url' => $url]);
        }

        return [
            'type_' . CrawlType::FOR_UPDATES->value => $out,
            'type_' . CrawlType::FULL_CATALOGUE->value => $codeArr,
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, array<string,mixed>>
     */
    protected function getEcode360LocationsAndTitles(): array
    {
        return [
            'FO3807' => ['location_id' => 7448, 'title' => 'Fox Crossing, WI'],
            'NE0870' => ['location_id' => 52364, 'title' => 'New Milford'],
            'BA2043' => ['location_id' => 74236, 'title' => 'Barnstable'],
            'CH2673' => ['location_id' => 63127, 'title' => 'Cherry Hill'],
            'MA2384' => ['location_id' => 76071, 'title' => 'Manheim City'],
            'MO2400' => ['location_id' => 76075, 'title' => 'Borough of Mount Joy'],
            'KA6368' => ['location_id' => 51110, 'title' => 'Katy City'],
            'SU0867' => ['location_id' => 4278, 'title' => 'Suffolk County, NY'],
            'IS0324' => ['location_id' => 64562, 'title' => 'Township of Islip'],
            'EA2319' => ['location_id' => 76060, 'title' => 'East Hempfield Township'],
            'HA1391' => ['location_id' => 75589, 'title' => 'City of Harrisburg, PA'],
            'MA2997' => ['location_id' => 76072, 'title' => 'Township of Manheim'],
            'MI1063' => ['location_id' => 75599, 'title' => 'Borough of Middletown'],
            'UP5026' => ['location_id' => 69162, 'title' => 'City of Upland, CA'],
            'VE5027' => ['location_id' => 69055, 'title' => 'City of Vernon, CA'],
            'HU4937' => ['location_id' => 69112, 'title' => 'City of Huntington Beach, CA'],
            'LA4953' => ['location_id' => 69109, 'title' => 'City of Laguna Beach'],
            'MI4968' => ['location_id' => 69069, 'title' => 'Mill Valley'],
            'SA4999' => ['location_id' => 2618, 'title' => 'Sacramento County'],
            'SA5006' => ['location_id' => 21009, 'title' => 'City of Santa Barbara'],
            'GL4929' => ['location_id' => 69002, 'title' => 'City of Glendale'],
            'RE4995' => ['location_id' => 69040, 'title' => 'City of Redondo Beach'],
            'SA5008' => ['location_id' => 69009, 'title' => 'City of Santa Monica'],
            'PL4987' => ['location_id' => 2615, 'title' => 'County of Placer'],
            'RO4998' => ['location_id' => 69121, 'title' => 'City of Roseville'],
            'PA4981' => ['location_id' => 69131, 'title' => 'City of Palm Desert'],
            'SU5020' => ['location_id' => 69211, 'title' => 'City of Sunnyvale'],
            'SA5009' => ['location_id' => 69237, 'title' => 'City of Santa Rosa'],
            'CL2905' => ['location_id' => 63591, 'title' => 'Township of Clark'],
            'BO3867' => ['location_id' => 63546, 'title' => 'Borough of Bound Brook'],
            'CL0218' => ['location_id' => 63272, 'title' => 'Borough of Closter'],
            'MA0608' => ['location_id' => 63506, 'title' => 'Borough of Madison'],
            'MO4033' => ['location_id' => 4185, 'title' => 'County of Monmouth'],
            'MA0373' => ['location_id' => 63461, 'title' => 'Township of Marlboro'],
            'ME1460' => ['location_id' => 63414, 'title' => 'Borough of Metuchen'],
            'ES1525' => ['location_id' => 4179, 'title' => 'Essex County'],
            'MO0769' => ['location_id' => 63347, 'title' => 'Township of Montclair'],
            'ME2189' => ['location_id' => 4183, 'title' => 'County of Mercer'],
            'WA0473' => ['location_id' => 63540, 'title' => 'Township of Wayne'],
            'HU2724' => ['location_id' => 4181, 'title' => 'Hudson County'],
            'ED0532' => ['location_id' => 63277, 'title' => 'Borough of Edgewater'],
            'EN4071' => ['location_id' => 63280, 'title' => 'Borough of Englewood Cliffs'],
            'MO0747' => ['location_id' => 63512, 'title' => 'Town of Morristown'],
            'NE4043' => ['location_id' => 63348, 'title' => 'City of Newark'],
            'PA1376' => ['location_id' => 63311, 'title' => 'Borough of Paramus'],
            'PA0882' => ['location_id' => 63517, 'title' => 'Township of Parsippany-Troy Hills'],
            'RE2655' => ['location_id' => 63471, 'title' => 'Borough of Rred Bank'],
            'RI1200' => ['location_id' => 63316, 'title' => 'Village of Ridgewood'],
            'WA1959' => ['location_id' => 63485, 'title' => 'Township of Wall'],
            'WE2734' => ['location_id' => 63354, 'title' => 'Township of West Orange'],
            'WO0158' => ['location_id' => 63334, 'title' => 'Borough of Woodcliff Lake'],
            'JE0687' => ['location_id' => 76433, 'title' => 'Borough of Jenkintown'],
            'LA1674' => ['location_id' => 76067, 'title' => 'City of Lancaster'],
            'AL4161' => ['location_id' => 76142, 'title' => 'City of Allentown'],
            'DE1994' => ['location_id' => 4665, 'title' => 'Delaware County'],
            'AL1955' => ['location_id' => 4644, 'title' => 'Allegheny County'],
            'NE1450' => ['location_id' => 75649, 'title' => 'Township of Newtown'],
            'HA2892' => ['location_id' => 74390, 'title' => 'Town of Hadley'],
            'AN1471' => ['location_id' => 74307, 'title' => 'Town of Andover'],
            'BE3069' => ['location_id' => 74457, 'title' => 'Town of Bellingham'],
            'WE0813' => ['location_id' => 74482, 'title' => 'Town of Weymouth'],
            'DE3083' => ['location_id' => 74462, 'title' => 'Town of Detham'],
            // 'UP5026' => ['location_id' => 69162, 'title' => 'City of Upland'],
            'NA4976' => ['location_id' => 21707, 'title' => 'City of Napa'],
            'MA5043' => ['location_id' => 191889, 'title' => 'City of Malibu'],
            'WE5031' => ['location_id' => 186511, 'title' => 'City of West Hollywood'],
            'HY0759' => ['location_id' => 191897, 'title' => 'City of Hyattsville'],
        ];
    }
}
