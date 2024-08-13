<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * slug: us-qcode
 * title: USA QCode
 * url: https://content.qcode.us/publications.html
 */
class Qcode extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/publications\.html$/')) {
                $this->getStartLinks($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/pub\/.*/')) {
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/pub\/.*/')) {
                $this->handleMeta($pageCrawl);
                $this->handleCapture($pageCrawl);
                $this->handleToc($pageCrawl);
            }
        }
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        $baseUrl = $pageCrawl->getFinalRedirectedUrl();
        $prefix = (string) Str::of($baseUrl)->after('qcode.us/lib/')->before('/pub');
        $locations = $this->getQcodeLocations();
        foreach ($dom->filter('section#main-content h1') as $link) {
            $title = $link->textContent;
            $title = Str::of($title)->replace(',', '');
            $titleNoSpaces = preg_replace('/\s+/', '-', strtolower($title));
            $primaryLocation = $locations[$prefix] ?? null;
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $baseUrl,
                'view_url' => $baseUrl,
                'source_unique_id' => $prefix . '/' . $titleNoSpaces,
                'language_code' => 'eng',
                'primary_location_id' => $primaryLocation,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        }
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

        return $doc;
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
                'query' => 'section#main-content',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'head > title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"css/app.css") and @rel="stylesheet"] | //head//link[contains(@href,"library.css") and @rel="stylesheet"] | //head//link[contains(@href,"content.css") and @rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'section.toc-extra',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'nav.item-toc',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'nav.nav-content-bottom',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.item-alert',
            ],
        ]);
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $criteria = function ($nodeDTO) {
            $n = $nodeDTO['node'];
            $tocContent = $n->textContent;
            if ($tocContent && (str_contains($tocContent, 'Expand List')
                || str_contains($tocContent, 'Note')
                || str_contains($tocContent, 'About This Publication')
                || str_contains($tocContent, 'Previous')
                || str_contains($tocContent, 'Next')
                || str_contains($tocContent, 'Show All')
                || str_contains($tocContent, 'Home Page')
                || str_contains($tocContent, 'Recent Amendments')
                || str_contains($tocContent, 'Ordinance List')
                || str_contains($tocContent, 'Statutory References')
                || str_contains($tocContent, 'Ordinance')
                || str_contains($tocContent, 'CodeAlert')
            )) {
                return;
            }
            $href = $nodeDTO['node']->getAttribute('href');

            return strtolower($n->nodeName) === 'a' && $href && $href !== '#';
        };

        $extractLabel = function ($nodeDTO) {
            $n = $nodeDTO['node'];

            return $n->textContent;
        };
        $extractLink = function ($nodeDTO) {
            $href = $nodeDTO['node']?->getAttribute('href');

            return 'https://library.qcode.us' . $href;
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            'section#main-content',
            criteriaFunc: $criteria,
            extractLabelFunc: $extractLabel,
            extractLinkFunc: $extractLink,
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
            'slug' => 'us-qcode',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://content.qcode.us/publications.html',
                    ]),
                ],
            ],
        ];
    }

    // /**
    //  * @codeCoverageIgnore
    //  *
    //  * @return array<string,array<UrlFrontierLink>>
    //  */
    // public function getAlertStartUrls(): array
    // {
    //     $out = [];
    //     foreach ($this->getQcodeDocs() as $juri) {
    //         $out[] = new UrlFrontierLink(['url' => $juri['url']]);
    //     }

    //     return ['type_' . CrawlType::FULL_CATALOGUE->value => $out];
    // }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getStartLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.qcp-publication-list p a[href*="/pub/"]',
            function (DOMElement $links) use ($pageCrawl) {
                /** @var DOMElement $href */
                $href = $links->getAttribute('href');

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $href]), true);
            });
    }

    // /**
    //  * @codeCoverageIgnore
    //  *
    //  * @return array<string, mixed>
    //  */
    // protected function getQcodeDocs(): array
    // {
    //     return [
    //         'buena_park_ca/city_code' => ['url' => 'https://library.qcode.us/lib/buena_park_ca/pub/city_code'],
    //         'costa_mesa_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/costa_mesa_ca/pub/municipal_code'],
    //         'downey_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/downey_ca/pub/municipal_code'],
    //         'encinitas_ca/cardiff_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/cardiff_sp'],
    //         'encinitas_ca/downtown_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/downtown_sp'],
    //         'encinitas_ca/encinitas_ranch_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/encinitas_ranch_sp'],
    //         'encinitas_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/municipal_code'],
    //         'encinitas_ca/home_depot_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/home_depot_sp'],
    //         'encinitas_ca/north_101_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/north_101_sp'],
    //         'escondido_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/escondido_ca/pub/municipal_code'],
    //         'glendale_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/glendale_ca/pub/municipal_code'],
    //         'glendora_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/glendora_ca/pub/municipal_code'],
    //         'industry_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/industry_ca/pub/municipal_code'],
    //         'la_habra_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/la_habra_ca/pub/municipal_code'],
    //         'lake_forest_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/lake_forest_ca/pub/municipal_code'],
    //         'manteca_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/manteca_ca/pub/municipal_code'],
    //         'monterey_park_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/monterey_park_ca/pub/municipal_code'],
    //         'norwalk_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/norwalk_ca/pub/municipal_code'],
    //         'pico_rivera_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/pico_rivera_ca/pub/municipal_code'],
    //         'pleasanton_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/pleasanton_ca/pub/municipal_code'],
    //         'rancho_cucamonga_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/rancho_cucamonga_ca/pub/municipal_code'],
    //         'redondo_beach_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/redondo_beach_ca/pub/municipal_code'],
    //         'roseville_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/roseville_ca/pub/municipal_code'],
    //         'sacramento_ca/city_code' => ['url' => 'https://library.qcode.us/lib/sacramento_ca/pub/city_code'],
    //         'san_juan_capistrano_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/san_juan_capistrano_ca/pub/municipal_code'],
    //         'santa_maria_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santa_maria_ca/pub/municipal_code'],
    //         'santa_barbara_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santa_barbara_ca/pub/municipal_code'],
    //         'santa_monica_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santa_monica_ca/pub/municipal_code'],
    //         'santee_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santee_ca/pub/municipal_code'],
    //         'santa_rosa_ca/city_code' => ['url' => 'https://library.qcode.us/lib/santa_rosa_ca/pub/city_code'],
    //         'seal_beach_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/seal_beach_ca/pub/municipal_code'],
    //         'stockton_ca/council_policy_manual' => ['url' => 'https://library.qcode.us/lib/stockton_ca/pub/council_policy_manual'],
    //         'sunnyvale_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/sunnyvale_ca/pub/municipal_code'],
    //         'temecula_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/temecula_ca/pub/municipal_code'],
    //         'upland_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/upland_ca/pub/municipal_code'],
    //         'westminster_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/westminster_ca/pub/municipal_code'],
    //         'woodland_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/woodland_ca/pub/municipal_code'],
    //         'placer_county_ca/county_code' => ['url' => 'https://library.qcode.us/lib/placer_county_ca/pub/county_code'],
    //         'sacramento_county_ca/county_code' => ['url' => 'https://library.qcode.us/lib/sacramento_county_ca/pub/county_code'],
    //     ];
    // }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, int>
     */
    protected function getQcodeLocations(): array
    {
        // Chik-Fil-A jurisdictions commented out
        return [
            'buena_park_ca' => 88739,
            'costa_mesa_ca' => 21053,
            'downey_ca' => 69006,
            'encinitas_ca' => 69178,
            'escondido_ca' => 69173,
            'glendale_ca' => 69002,
            'glendora_ca' => 69013,
            'industry_ca' => 69018,
            'la_habra_ca' => 69103,
            'lake_forest_ca' => 88741,
            'manteca_ca' => 69188,
            'monterey_park_ca' => 69050,
            'norwalk_ca' => 69019,
            'pico_rivera_ca' => 69017,
            'placer_county_ca' => 2615,
            'pleasanton_ca' => 68913,
            'rancho_cucamonga_ca' => 21728,
            'redondo_beach_ca' => 69040,
            'roseville_ca' => 69121,
            'sacramento_ca' => 69147,
            'sacramento_county_ca' => 2618,
            'san_juan_capistrano_ca' => 88734,
            'santa_barbara_ca' => 21009,
            'santa_maria_ca' => 20993,
            'santa_monica_ca' => 69009,
            'santa_rosa_ca' => 69237,
            'santee_ca' => 69184,
            'seal_beach_ca' => 69102,
            'stockton_ca' => 69190,
            'sunnyvale_ca' => 69211,
            'temecula_ca' => 20863, // found here https://ecode360.com/TE5022
            'upland_ca' => 69162,
            'westminster_ca' => 69118,
            'woodland_ca' => 69267,
            'el_cajon_ca' => 173449,
            'carlsbad_ca' => 69179,
        ];
    }
}
