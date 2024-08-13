<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Support\DomClosest;
use DOMElement;
use DOMNode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * @codeCoverageIgnore
 *
 * slug: us-qcode-ordinances
 * title: USA QCode Ordinances
 * url: https://content.qcode.us/publications.html
 */
class QcodeOrdinances extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/(\/alerts|\/revisions\.html)/')) {
            $this->handleAlertsPage($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/content\.qcode\.us\/.*\.pdf$/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    protected function handleAlertsPage(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        $baseUrl = $pageCrawl->getFinalRedirectedUrl();
        $prefix = str_replace(['https://library.qcode.us/lib/', '/pub', '/alerts'], '', $baseUrl);
        $jurisdiction = $dom->filter('#breadcrumbs > ul > li > a')->getNode(0)?->textContent ?? '';
        $locations = $this->getQcodeLocations();
        foreach ($dom->filter('table tr > td:nth-child(1) > p  a') as $link) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $title = $link->textContent;
            $docMeta = new DocMetaDto();
            $titleNoSpaces = preg_replace('/\s+/', '', $title);
            $docMeta->source_unique_id = 'updates_' . $prefix . '/' . $titleNoSpaces;
            $docMeta->title = $jurisdiction . ' ' . $title;
            $docMeta->work_type = 'ordinance';
            $closest = app(DomClosest::class);
            /** @var DOMNode $link */
            /** @var Crawler $tr */
            $tr = $closest->closest($link, 'tr');

            $date = $tr->filter('td:nth-child(2) p')->getNode(0)?->textContent ?? null;
            try {
                if ($date) {
                    /** @var Carbon */
                    $workDate = Carbon::createFromFormat('n/j/y H:i', trim($date) . ' 12:00');
                    $docMeta->work_date = $workDate;
                }
            } catch (Throwable $th) {
            }
            $summary = $tr->filter('td:nth-child(3)')->getNode(0)?->textContent ?? null;
            $docMeta->summary = $summary;
            $number = str_replace('Ordinance ', '', $title);
            $docMeta->work_number = $number;
            $prefix = (string) Str::of($baseUrl)->after('qcode.us/lib/')->before('/pub');
            $docMeta->primary_location = (string) ($locations[$prefix] ?? '');
            $docMeta->language_code = 'eng';

            $href = str_contains($href, 'revisions/')
                ? str_replace('revisions.html', $href, $baseUrl)  // e.g. on this page http://qcode.us/codes/santarosa/revisions.html
                : $href; // e.g. on this page https://library.qcode.us/lib/yountville_ca/pub/municipal_code/alerts
            $docMeta->download_url = $href;
            $docMeta->source_url = $href;

            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => $title]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'for_update' => true,
            'throttle_requests' => 200,
            'start_urls' => $this->getAlertStartUrls(),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string,array<UrlFrontierLink>>
     */
    public function getAlertStartUrls(): array
    {
        $out = [];
        foreach ($this->getQcodeDocs() as $juri) {
            $out[] = new UrlFrontierLink(['url' => $juri['url']]);
        }

        return ['type_' . CrawlType::FOR_UPDATES->value => $out];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, mixed>
     */
    protected function getQcodeDocs(): array
    {
        $arr = [
            // "https://library.qcode.us/lib/american_canyon_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/atascadero_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/bell_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/bellflower_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/brentwood_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/buellton_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/buena_park_ca/pub/city_code/alerts",
            // "https://library.qcode.us/lib/carlsbad_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/costa_mesa_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/cathedral_city_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/cypress_ca/pub/city_code/alerts",
            // "https://library.qcode.us/lib/dana_point_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/crescent_city_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/desert_hot_springs_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/downey_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/el_cajon_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/encinitas_ca/pub/cardiff_sp/alerts",
            // "https://library.qcode.us/lib/encinitas_ca/pub/downtown_sp/alerts",
            // "https://library.qcode.us/lib/encinitas_ca/pub/encinitas_ranch_sp/alerts",
            // "https://library.qcode.us/lib/encinitas_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/encinitas_ca/pub/home_depot_sp/alerts",
            // "https://library.qcode.us/lib/encinitas_ca/pub/north_101_sp/alerts",
            // "https://library.qcode.us/lib/fountain_valley_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/escondido_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/glendale_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/glendora_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/garden_grove_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/goleta_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/hanford_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/happy_valley_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/hawthorne_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/hillsboro_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/huntington_beach_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/huntington_park_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/imperial_beach_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/industry_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/indian_wells_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/inglewood_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/kalispell_mt/pub/city_code/alerts",
            // "https://library.qcode.us/lib/kauai_county_hi/pub/county_code/alerts",
            // "https://library.qcode.us/lib/la_canada_flintridge_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/la_habra_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/la_verne_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/laguna_beach_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/lake_forest_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/lassen_county_ca/pub/county_code/alerts",
            // "https://library.qcode.us/lib/lathrop_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/lawndale_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/live_oak_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/loma_linda_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/loomis_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/los_altos_hills_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/los_alamitos_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/malibu_ca/pub/general_plan/alerts",
            // "https://library.qcode.us/lib/malibu_ca/pub/local_coastal_program/alerts",
            // "https://library.qcode.us/lib/malibu_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/marin_mwd_ca/pub/code/alerts",
            // "https://library.qcode.us/lib/manteca_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/merced_county_ca/pub/county_code/alerts",
            // "https://library.qcode.us/lib/mill_valley_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/milwaukie_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/monterey_park_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/moorpark_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/moreno_valley_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/mt_crested_butte_co/pub/town_code/alerts",
            // "https://library.qcode.us/lib/napa_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/nevada_county_ca/pub/county_code/alerts",
            // "https://library.qcode.us/lib/norwalk_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/orange_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/oroville_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/palm_desert_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/palm_springs_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/paramount_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/pico_rivera_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/placentia_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/placer_county_ca/pub/county_code/alerts",
            // "https://library.qcode.us/lib/pleasanton_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/plymouth_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/port_orford_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/rancho_mirage_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/rancho_cucamonga_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/redondo_beach_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/rio_vista_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/roseville_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/sacramento_county_ca/pub/county_code/alerts",
            // "https://library.qcode.us/lib/sacramento_ca/pub/city_code/alerts",
            // "https://library.qcode.us/lib/san_bruno_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/san_dimas_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/san_juan_capistrano_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/san_leandro_ca/pub/zoning_code/alerts",
            // "https://library.qcode.us/lib/san_leandro_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/san_leandro_ca/pub/admin_code/alerts",
            // "https://library.qcode.us/lib/sand_city_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/santa_maria_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/santa_barbara_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/santa_monica_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/santee_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/santa_rosa_ca/pub/city_code/alerts",
            // "https://library.qcode.us/lib/seal_beach_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/saratoga_wy/pub/town_code/alerts",
            // "https://library.qcode.us/lib/sheridan_wy/pub/city_code/alerts",
            // "https://library.qcode.us/lib/south_el_monte_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/south_san_francisco_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/solvang_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/springfield_or/pub/development_code/alerts",
            // "https://library.qcode.us/lib/springfield_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/stockton_ca/pub/council_policy_manual/alerts",
            // "https://library.qcode.us/lib/stanton_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/stockton_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/sunnyvale_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/susanville_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/temecula_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/the_dalles_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/tigard_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/union_city_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/upland_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/walnut_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/vernon_ca/pub/city_code/alerts",
            // "https://library.qcode.us/lib/warrenton_or/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/waterford_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/west_hollywood_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/westminster_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/wheatland_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/west_sacramento_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/wildomar_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/woodland_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/yucaipa_ca/pub/municipal_code/alerts",
            // "https://library.qcode.us/lib/yountville_ca/pub/municipal_code/alerts",
        ];

        return [
            //            'buena_park_ca/city_code' => ['url' => 'https://library.qcode.us/lib/buena_park_ca/pub/city_code/alerts'],
            //            'costa_mesa_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/costa_mesa_ca/pub/municipal_code/alerts'],
            //            'downey_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/downey_ca/pub/municipal_code/alerts'],
            //            'encinitas_ca/cardiff_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/cardiff_sp/alerts'],
            //            'encinitas_ca/downtown_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/downtown_sp/alerts'],
            //            'encinitas_ca/encinitas_ranch_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/encinitas_ranch_sp/alerts'],
            //            'encinitas_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/municipal_code/alerts'],
            //            'encinitas_ca/home_depot_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/home_depot_sp/alerts'],
            //            'encinitas_ca/north_101_sp' => ['url' => 'https://library.qcode.us/lib/encinitas_ca/pub/north_101_sp/alerts'],
            //            'escondido_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/escondido_ca/pub/municipal_code/alerts'],
            //            'glendale_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/glendale_ca/pub/municipal_code/alerts'],
            //            'glendora_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/glendora_ca/pub/municipal_code/alerts'],
            //            'industry_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/industry_ca/pub/municipal_code/alerts'],
            //            'la_habra_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/la_habra_ca/pub/municipal_code/alerts'],
            //            'lake_forest_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/lake_forest_ca/pub/municipal_code/alerts'],
            //            'manteca_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/manteca_ca/pub/municipal_code/alerts'],
            //            'monterey_park_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/monterey_park_ca/pub/municipal_code/alerts'],
            //            'norwalk_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/norwalk_ca/pub/municipal_code/alerts'],
            'pico_rivera_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/pico_rivera_ca/pub/municipal_code/alerts'],
            //            'pleasanton_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/pleasanton_ca/pub/municipal_code/alerts'],
            //            'rancho_cucamonga_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/rancho_cucamonga_ca/pub/municipal_code/alerts'],
            //            'redondo_beach_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/redondo_beach_ca/pub/municipal_code/alerts'],
            //            'roseville_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/roseville_ca/pub/municipal_code/alerts'],
            'sacramento_ca/city_code' => ['url' => 'https://library.qcode.us/lib/sacramento_ca/pub/city_code/alerts'],
            //            'san_juan_capistrano_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/san_juan_capistrano_ca/pub/municipal_code/alerts'],
            //            'santa_maria_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santa_maria_ca/pub/municipal_code/alerts'],
            //            'santa_barbara_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santa_barbara_ca/pub/municipal_code/alerts'],
            //            'santa_monica_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santa_monica_ca/pub/municipal_code/alerts'],
            //            'santee_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/santee_ca/pub/municipal_code/alerts'],
            //            'santa_rosa_ca/city_code' => ['url' => 'https://library.qcode.us/lib/santa_rosa_ca/pub/city_code/alerts'],
            //            'seal_beach_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/seal_beach_ca/pub/municipal_code/alerts'],
            //            'stockton_ca/council_policy_manual' => ['url' => 'https://library.qcode.us/lib/stockton_ca/pub/council_policy_manual/alerts'],
            //            'sunnyvale_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/sunnyvale_ca/pub/municipal_code/alerts'],
            'temecula_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/temecula_ca/pub/municipal_code/alerts'],
            //            'upland_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/upland_ca/pub/municipal_code/alerts'],
            //            'westminster_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/westminster_ca/pub/municipal_code/alerts'],
            'woodland_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/woodland_ca/pub/municipal_code/alerts'],
            //            'placer_county_ca/county_code' => ['url' => 'https://library.qcode.us/lib/placer_county_ca/pub/county_code/alerts'],
            'sacramento_county_ca/county_code' => ['url' => 'https://library.qcode.us/lib/sacramento_county_ca/pub/county_code/alerts'],
            'el_cajon_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/el_cajon_ca/pub/municipal_code/alerts'],
            'carlsbad_ca/municipal_code' => ['url' => 'https://library.qcode.us/lib/carlsbad_ca/pub/municipal_code/alerts'],
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, int>
     */
    protected function getQcodeLocations(): array
    {
        // Chik-Fil-A jurisdictions commented out
        return [
            //            'buena_park_ca' => 88739,
            //            'costa_mesa_ca' => 21053,
            //            'downey_ca' => 69006,
            //            'encinitas_ca' => 69178,
            //            'escondido_ca' => 69173,
            //            'glendale_ca' => 69002,
            //            'glendora_ca' => 69013,
            //            'industry_ca' => 69018,
            //            'la_habra_ca' => 69103,
            //            'lake_forest_ca' => 88741,
            //            'manteca_ca' => 69188,
            //            'monterey_park_ca' => 69050,
            //            'norwalk_ca' => 69019,
            'pico_rivera_ca' => 69017,
            //            'placer_county_ca' => 2615,
            //            'pleasanton_ca' => 68913,
            //            'rancho_cucamonga_ca' => 21728,
            //            'redondo_beach_ca' => 69040,
            //            'roseville_ca' => 69121,
            'sacramento_ca' => 69147,
            'sacramento_county_ca' => 2618,
            //            'san_juan_capistrano_ca' => 88734,
            //            'santa_barbara_ca' => 21009,
            //            'santa_maria_ca' => 20993,
            //            'santa_monica_ca' => 69009,
            //            'santa_rosa_ca' => 69237,
            //            'santee_ca' => 69184,
            //            'seal_beach_ca' => 69102,
            //            'stockton_ca' => 69190,
            //            'sunnyvale_ca' => 69211,
            'temecula_ca' => 20863,
            //            'upland_ca' => 69162,
            //            'westminster_ca' => 69118,
            'woodland_ca' => 69267,
            'el_cajon_ca' => 173449,
            'carlsbad_ca' => 69179,
        ];
    }
}
