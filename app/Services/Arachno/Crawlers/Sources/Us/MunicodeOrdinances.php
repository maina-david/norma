<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;

/**
 * @codeCoverageIgnore
 *
 * slug: us-municode-ordinances
 * title: Municode Ordinances
 * url: https://library.municode.com.
 */
class MunicodeOrdinances extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/api\.municode\.com\/ordinancesToc/')) {
            $this->handleMunicodeToc($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/api\.municode\.com\/CoreContent\/Ordinances/')) {
            $this->handleOrdinancesForYear($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/mcclibraryfunctions\.azurewebsites\.us\/api\/ordinanceDownload\//')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    protected function handleMunicodeToc(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        if (!isset($json['Children']) || !isset($json['Children'][0])) {
            return;
        }
        $year = $json['Children'][0]['Id'];
        $productId = $json['Children'][0]['ParentId'];

        $frontierLink = new UrlFrontierLink(['url' => 'https://api.municode.com/CoreContent/Ordinances?nodeId=' . $year . '&productId=' . $productId, 'anchor_text' => $year]);
        $this->arachno->followLink($pageCrawl, $frontierLink);
    }

    protected function handleOrdinancesForYear(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $locations = $this->getMunicodeLocations();
        foreach ($json['ords'] as $ord) {
            $id = $ord['Id'];
            $productId = $ord['ProductId'];
            $title = $ord['Title'];
            $docMeta = new DocMetaDto();
            $downloadUrl = 'https://mcclibraryfunctions.azurewebsites.us/api/ordinanceDownload/' . $productId . '/' . $id . '/pdf';
            $location = $locations[$productId] ?? null;
            $docMeta->setValues([
                'source_unique_id' => $productId . '-' . $id,
                'title' => $title,
                'work_number' => $ord['DocumentTitle'],
                'summary' => $ord['Description'],
                'work_type' => 'ordinance',
                'download_url' => $downloadUrl,
                'primary_location' => $location,
                'language_code' => 'eng',
            ]);
            $date = $ord['SortDate'] ?? $ord['AdoptionDate'];
            if ($date) {
                $docMeta->setValues([
                    'work_date' => str_replace('T00:00:00', '', $date),
                ]);
            }
            $frontierLink = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => $title]);
            $frontierLink->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $frontierLink, true);
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
            'slug' => 'us-municode-ordinances',
            'for_update' => true,
            'throttle_requests' => 800,
            'start_urls' => $this->getMunicodeStartUrls(),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, array<UrlFrontierLink>>
     */
    private function getMunicodeStartUrls(): array
    {
        $arr = [
            'https://api.municode.com/ordinancesToc?productId=11378', // https://library.municode.com/ca/orange_county/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16274', // https://library.municode.com/ca/los_angeles_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16186', // https://library.municode.com/ca/alameda_county/ordinances/administrative_code
            'https://api.municode.com/ordinancesToc?productId=16425', // https://library.municode.com/ca/alameda_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16286', // https://library.municode.com/ca/contra_costa_county/ordinances/ordinance_code
            'https://api.municode.com/ordinancesToc?productId=16476', // https://library.municode.com/ca/marin_county/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=16320', // https://library.municode.com/ca/riverside_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16452', // https://library.municode.com/ca/san_joaquin_county/ordinances/development_title
            'https://api.municode.com/ordinancesToc?productId=16510', // https://library.municode.com/ca/san_joaquin_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16608', // https://library.municode.com/ca/san_luis_obispo_county/ordinances/county_code
            'https://api.municode.com/ordinancesToc?productId=13790', // https://library.municode.com/ca/santa_clara_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16331', // https://library.municode.com/ca/sonoma_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16111', // https://library.municode.com/ca/monterey_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16622', // https://library.municode.com/ca/shasta_county/ordinances/code
            'https://api.municode.com/ordinancesToc?productId=16029', // https://library.municode.com/ca/san_mateo_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16115', // https://library.municode.com/ca/long_beach/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=16854', // https://library.municode.com/ca/long_beach/ordinances/city_charter
            'https://api.municode.com/ordinancesToc?productId=16551', // https://library.municode.com/ca/pasadena/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16695', // https://library.municode.com/ca/whittier/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14478', // https://library.municode.com/ca/fresno/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16532', // https://library.municode.com/ca/novato/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10941', // https://library.municode.com/ca/irvine/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13239', // https://library.municode.com/ca/irvine/ordinances/zoning
            'https://api.municode.com/ordinancesToc?productId=12544', // https://library.municode.com/ca/laguna_niguel/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14452', // https://library.municode.com/ca/santa_ana/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14596', // https://library.municode.com/ca/riverside/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16575', // https://library.municode.com/ca/rialto/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16430', // https://library.municode.com/ca/la_mesa/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14631', // https://library.municode.com/ca/oceanside/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15572', // https://library.municode.com/ca/san_marcos/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16586', // https://library.municode.com/ca/rohnert_park/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10135', // https://library.municode.com/ca/san_buenaventura/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13695', // https://library.municode.com/ca/san_buenaventura/ordinances/administrative_policies
            'https://api.municode.com/ordinancesToc?productId=16106', // https://library.municode.com/ca/vallejo/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=16572', // https://library.municode.com/ca/redding/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15015', // https://library.municode.com/ca/eastvale/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16553', // https://library.municode.com/ca/perris/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16091', // https://library.municode.com/ca/redwood_city/ordinances/zoning_code
            'https://api.municode.com/ordinancesToc?productId=16606', // https://library.municode.com/ca/san_clemente/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11504', // https://library.municode.com/ca/west_covina/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16197', // https://library.municode.com/ca/arcadia/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15488', // https://library.municode.com/ca/hayward/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=11760', // https://library.municode.com/ca/los_gatos/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10962', // https://library.municode.com/ca/el_centro/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12598', // https://library.municode.com/wi/milwaukee_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15302', // https://library.municode.com/tx/austin/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15303', // https://library.municode.com/tx/austin/ordinances/land_development_code
            'https://api.municode.com/ordinancesToc?productId=15304', // https://library.municode.com/tx/austin/ordinances/building_criteria_manual
            'https://api.municode.com/ordinancesToc?productId=15305', // https://library.municode.com/tx/austin/ordinances/drainage_criteria_manual
            'https://api.municode.com/ordinancesToc?productId=15306', // https://library.municode.com/tx/austin/ordinances/environmental_criteria_manual
            'https://api.municode.com/ordinancesToc?productId=15307', // https://library.municode.com/tx/austin/ordinances/fire_protection_criteria_manual
            'https://api.municode.com/ordinancesToc?productId=15308', // https://library.municode.com/tx/austin/ordinances/standard_specifications_manual
            'https://api.municode.com/ordinancesToc?productId=15309', // https://library.municode.com/tx/austin/ordinances/standards_manual
            'https://api.municode.com/ordinancesToc?productId=15310', // https://library.municode.com/tx/austin/ordinances/standards_manual
            'https://api.municode.com/ordinancesToc?productId=15312', // https://library.municode.com/tx/austin/ordinances/transportation_criteria_manual
            'https://api.municode.com/ordinancesToc?productId=15523', // https://library.municode.com/tx/austin/ordinances/utilities_criteria_manual
            'https://api.municode.com/ordinancesToc?productId=10123', // https://library.municode.com/tx/houston/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11508', // https://library.municode.com/tx/san_antonio/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14228', // https://library.municode.com/tx/san_antonio/ordinances/unified_development_code
            'https://api.municode.com/ordinancesToc?productId=16180', // https://library.municode.com/tx/el_paso/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10376', // https://library.municode.com/ga/atlanta/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14972', // https://library.municode.com/ca/fresno_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16251', // https://library.municode.com/ca/kern_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16322', // https://library.municode.com/ca/santa_barbara_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16749', // https://library.municode.com/ca/ventura_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16410', // https://library.municode.com/ca/imperial_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10418', // https://library.municode.com/ca/azusa/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16689', // https://library.municode.com/ca/westlake_village/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11307', // https://library.municode.com/ca/tustin/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16034', // https://library.municode.com/ca/chino_hills/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16516', // https://library.municode.com/ca/national_city/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14367', // https://library.municode.com/ca/san_jose/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16629', // https://library.municode.com/ca/simi_valley/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16597', // https://library.municode.com/ca/salinas/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16050', // https://library.municode.com/ca/rocklin/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16027', // https://library.municode.com/ar/pulaski_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13387', // https://library.municode.com/ar/maumelle/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13865', // https://library.municode.com/wi/neenah/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16602', // https://library.municode.com/ut/salt_lake_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=41106', // https://library.municode.com/ut/weber_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10344', // https://library.municode.com/sc/aiken_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10724', // https://library.municode.com/sc/aiken/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=19956', // https://library.municode.com/tx/carrollton/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10816', // https://library.municode.com/ga/fulton_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13805', // https://library.municode.com/il/cook_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12174', // https://library.municode.com/fl/jacksonville/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10132', // https://library.municode.com/fl/tampa/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12016', // https://library.municode.com/in/indianapolis_-_marion_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13981', // https://library.municode.com/mi/ypsilanti/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10156', // https://library.municode.com/mo/kansas_city/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16214', // https://library.municode.com/nv/clark_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14787', // https://library.municode.com/nv/las_vegas/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16028', // https://library.municode.com/oh/akron/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=19996', // https://library.municode.com/oh/cincinnati/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16219', // https://library.municode.com/oh/columbus/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13723', // https://library.municode.com/oh/dayton/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10121', // https://library.municode.com/va/norfolk/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10257', // https://library.municode.com/co/denver/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14214', // https://library.municode.com/tn/metro_government_of_nashville_and_davidson_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11490', // https://library.municode.com/mn/minneapolis/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10061', // https://library.municode.com/mn/st._paul/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11926', // https://library.municode.com/md/baltimore_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15458', // https://library.municode.com/az/tempe/ordinances/city_code
            'https://api.municode.com/ordinancesToc?productId=18013', // https://library.municode.com/co/broomfield/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=11804', // https://library.municode.com/ga/troup_county/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11265', // https://library.municode.com/al/mobile/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11385', // https://library.municode.com/ky/owensboro/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10907', // https://library.municode.com/nc/hendersonville/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14632', // https://library.municode.com/GA/Roswell/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12776', // https://library.municode.com/nc/buncombe_county/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15367', // https://library.municode.com/nc/fuquay-varina/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15701', // https://library.municode.com/mn/plymouth/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15875', // https://library.municode.com/or/wilsonville/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16248', // https://library.municode.com/ca/carpinteria/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=18039', // https://library.municode.com/co/centennial/codes/municipal_code
            'https://api.municode.com/ordinancesToc?productId=16509', // https://library.municode.com/il/mundelein/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15095', // https://library.municode.com/ca/el_dorado_county/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16610', // https://library.municode.com/ca/san_rafael/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16308', // https://library.municode.com/ca/oakland/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16579', // https://library.municode.com/ca/richmond/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16460', // https://library.municode.com/ca/los_altos/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16435', // https://library.municode.com/ca/lafayette/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15557', // https://library.municode.com/ca/del_mar/codes/municipal_code
            'https://api.municode.com/ordinancesToc?productId=16242', // https://library.municode.com/ca/campbell/codes/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11002', // https://library.municode.com/al/hoover/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10763', // https://library.municode.com/ar/fayetteville/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14680', // https://library.municode.com/md/howard_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11782', // https://library.municode.com/mi/ann_arbor/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13170', // https://library.municode.com/mi/rochester_hills/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11507', // https://library.municode.com/mi/west_bloomfield/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13032', // https://library.municode.com/mi/wayne_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10649', // https://library.municode.com/mi/detroit/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10945', // https://library.municode.com/ne/omaha/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14896', // https://library.municode.com/nv/henderson/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15498', // https://library.municode.com/nv/washoe_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=15375', // https://library.municode.com/oh/upper_arlington/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13857', // https://library.municode.com/wa/seattle/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=16720', // https://library.municode.com/wa/thurston_county/ordinances/code_of_ordinances
            // 'https://api.municode.com/ordinancesToc?productId=12598', // https://library.municode.com/wi/milwaukee_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16684', // https://library.municode.com/wi/wauwatosa/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=50000', // https://library.municode.com/wi/madison/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16574', // https://library.municode.com/ca/redwood_city/ordinances/city_code
            'https://api.municode.com/ordinancesToc?productId=11910', // https://library.municode.com/ms/corinth/ordinances/code_of_ordinances
            // 'https://api.municode.com/ordinancesToc?productId=18039', // https://library.municode.com/co/centennial/ordinances/municipal_code - Duplicate
            'https://api.municode.com/ordinancesToc?productId=18113', // https://library.municode.com/co/basalt/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=18020', // https://library.municode.com/co/boulder/ordinances/municipal_code
            'https://api.municode.com/ordinancesToc?productId=14590', // https://library.municode.com/co/longmont/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=18088', // https://library.municode.com/co/superior/ordinances/municipal_code
            // 'https://api.municode.com/ordinancesToc?productId=10376', // https://library.municode.com/ga/atlanta/ordinances/code_of_ordinances - Duplicate
            'https://api.municode.com/ordinancesToc?productId=12100', // https://library.municode.com/ga/alpharetta/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14670', // https://library.municode.com/ga/jackson_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14385', // https://library.municode.com/ga/sandy_springs/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10637', // https://library.municode.com/ga/dekalb_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10572', // https://library.municode.com/ga/cobb_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12813', // https://library.municode.com/ga/kennesaw/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12110', // https://library.municode.com/ga/decatur/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12224', // https://library.municode.com/ga/marietta/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16289', // https://library.municode.com/hi/county_of_maui/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13492', // https://library.municode.com/in/hamilton_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=17131', // https://library.municode.com/in/st._joseph_county/ordinances/code_of_ordinances
            // 'https://api.municode.com/ordinancesToc?productId=11490', // https://library.municode.com/mn/minneapolis/ordinances/code_of_ordinances - Duplicate
            'https://api.municode.com/ordinancesToc?productId=15157', // https://library.municode.com/mn/edina/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14365', // https://library.municode.com/mn/woodbury/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14983', // https://library.municode.com/nc/orange_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=19952', // https://library.municode.com/nc/chapel_hill/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10312', // https://library.municode.com/nc/raleigh/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=19970', // https://library.municode.com/nc/charlotte/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12650', // https://library.municode.com/nc/durham_county/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=19967', // https://library.municode.com/nc/durham/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=16889', // https://library.municode.com/ma/cambridge/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=13043', // https://library.municode.com/ma/medford/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=11580', // https://library.municode.com/ma/somerville/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12962', // https://library.municode.com/al/huntsville/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14276', // https://library.municode.com/al/mountain_brook/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10158', // https://library.municode.com/az/chandler/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=10822', // https://library.municode.com/md/gaithersburg/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14917', // https://library.municode.com/md/rockville/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=12209', // https://library.municode.com/md/chevy_chase/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14755', // https://library.municode.com/mt/bozeman/ordinances/code_of_ordinances
            'https://api.municode.com/ordinancesToc?productId=14160', // https://library.municode.com/nv/reno/ordinances/administrative_code
        ];
        $out = [];
        foreach ($arr as $url) {
            $out[] = new UrlFrontierLink(['url' => $url]);
        }

        return ['type_' . CrawlType::FOR_UPDATES->value => $out];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string|int, int>
     */
    private function getMunicodeLocations(): array
    {
        // Chik-Fil-A jurisdictions commented out
        return [
            '11378' => 2614, // https://library.municode.com/ca/orange_county/codes/code_of_ordinances
            '16274' => 2603, // https://library.municode.com/ca/los_angeles_county/ordinances/code_of_ordinances
            '16186' => 68911, // https://library.municode.com/ca/alameda_county/ordinances/administrative_code
            '16425' => 68911, // https://library.municode.com/ca/alameda_county/ordinances/code_of_ordinances
            '16286' => 2591, // https://library.municode.com/ca/contra_costa_county/ordinances/ordinance_code
            '16476' => 2605, // https://library.municode.com/ca/marin_county/ordinances/municipal_code
            '16320' => 2617, // https://library.municode.com/ca/riverside_county/ordinances/code_of_ordinances
            //            '16452' => 2623, // https://library.municode.com/ca/san_joaquin_county/ordinances/development_title
            //            '16510' => 2623, // https://library.municode.com/ca/san_joaquin_county/ordinances/code_of_ordinances
            '16608' => 2624, // https://library.municode.com/ca/san_luis_obispo_county/ordinances/county_code
            '13790' => 2627, // https://library.municode.com/ca/santa_clara_county/ordinances/code_of_ordinances
            '16331' => 2633, // https://library.municode.com/ca/sonoma_county/ordinances/code_of_ordinances
            '16111' => 2611, // https://library.municode.com/ca/monterey_county/ordinances/code_of_ordinances
            //            '16622' => 2629, // https://library.municode.com/ca/shasta_county/ordinances/code
            '16029' => 2625, // https://library.municode.com/ca/san_mateo_county/ordinances/code_of_ordinances
            '16115' => 69045, // https://library.municode.com/ca/long_beach/ordinances/municipal_code
            //            '16854' => 69045, // https://library.municode.com/ca/long_beach/ordinances/city_charter
            '16551' => 69038, // https://library.municode.com/ca/pasadena/ordinances/code_of_ordinances
            //            '16695' => 69048, // https://library.municode.com/ca/whittier/ordinances/code_of_ordinances
            '14478' => 68956, // https://library.municode.com/ca/fresno/ordinances/code_of_ordinances
            '16532' => 22196, // https://library.municode.com/ca/novato/ordinances/code_of_ordinances
            '10941' => 88914, // https://library.municode.com/ca/irvine/ordinances/code_of_ordinances
            //            '13239' => 88914, // https://library.municode.com/ca/irvine/ordinances/zoning
            '12544' => 88916, // https://library.municode.com/ca/laguna_niguel/ordinances/code_of_ordinances
            //            '14452' => 69105, // https://library.municode.com/ca/santa_ana/ordinances/code_of_ordinances
            //            '14596' => 69142, // https://library.municode.com/ca/riverside/ordinances/code_of_ordinances
            //            '16575' => 21273, // https://library.municode.com/ca/rialto/ordinances/code_of_ordinances
            //            '16430' => 22199, // https://library.municode.com/ca/la_mesa/ordinances/code_of_ordinances
            '14631' => 69180, // https://library.municode.com/ca/oceanside/ordinances/code_of_ordinances
            //            '15572' => 69176, // https://library.municode.com/ca/san_marcos/ordinances/code_of_ordinances
            //            '16586' => 69236, // https://library.municode.com/ca/rohnert_park/ordinances/code_of_ordinances
            //            '10135' => 21548, // https://library.municode.com/ca/san_buenaventura/ordinances/code_of_ordinances
            //            '13695' => 21548, // https://library.municode.com/ca/san_buenaventura/ordinances/administrative_policies
            //            '16106' => 69230, // https://library.municode.com/ca/vallejo/ordinances/municipal_code
            //            '16572' => 69283, // https://library.municode.com/ca/redding/ordinances/code_of_ordinances
            //            '15015' => 69141, // https://library.municode.com/ca/eastvale/ordinances/code_of_ordinances
            //            '16553' => 69138, // https://library.municode.com/ca/perris/ordinances/code_of_ordinances
            '16091' => 69197, // https://library.municode.com/ca/redwood_city/ordinances/zoning_code
            //            '16606' => 69107, // https://library.municode.com/ca/san_clemente/ordinances/code_of_ordinances
            //            '11504' => 69064, // https://library.municode.com/ca/west_covina/ordinances/code_of_ordinances
            //            '16197' => 69008, // https://library.municode.com/ca/arcadia/ordinances/code_of_ordinances
            '15488' => 68912, // https://library.municode.com/ca/hayward/ordinances/municipal_code
            '11760' => 98260, // https://library.municode.com/ca/los_gatos/ordinances/code_of_ordinances
            '10962' => 68974, // https://library.municode.com/ca/el_centro/ordinances/code_of_ordinances
            '12598' => 5485, // https://library.municode.com/wi/milwaukee_county/ordinances/code_of_ordinances
            '15302' => 51749, // https://library.municode.com/tx/austin/ordinances/code_of_ordinances
            '15303' => 51749, // https://library.municode.com/tx/austin/ordinances/land_development_code
            '15304' => 51749, // https://library.municode.com/tx/austin/ordinances/building_criteria_manual
            '15305' => 51749, // https://library.municode.com/tx/austin/ordinances/drainage_criteria_manual
            '15306' => 51749, // https://library.municode.com/tx/austin/ordinances/environmental_criteria_manual
            '15307' => 51749, // https://library.municode.com/tx/austin/ordinances/fire_protection_criteria_manual
            '15308' => 51749, // https://library.municode.com/tx/austin/ordinances/standard_specifications_manual
            '15309' => 51749, // https://library.municode.com/tx/austin/ordinances/standards_manual
            '15310' => 51749, // https://library.municode.com/tx/austin/ordinances/standards_manual
            '15312' => 51749, // https://library.municode.com/tx/austin/ordinances/transportation_criteria_manual
            '15523' => 51749, // https://library.municode.com/tx/austin/ordinances/utilities_criteria_manual
            '10123' => 51109, // https://library.municode.com/tx/houston/ordinances/code_of_ordinances
            '11508' => 50612, // https://library.municode.com/tx/san_antonio/ordinances/code_of_ordinances
            '14228' => 50612, // https://library.municode.com/tx/san_antonio/ordinances/unified_development_code
            '16180' => 4991, // https://library.municode.com/tx/el_paso/ordinances/code_of_ordinances
            '10376' => 44962, // https://library.municode.com/ga/atlanta/ordinances/code_of_ordinances
            //            '14972' => 2594, // https://library.municode.com/ca/fresno_county/ordinances/code_of_ordinances
            //            '16251' => 2599, // https://library.municode.com/ca/kern_county/ordinances/code_of_ordinances
            //            '16322' => 2626, // https://library.municode.com/ca/santa_barbara_county/ordinances/code_of_ordinances
            //            '16749' => 2640, // https://library.municode.com/ca/ventura_county/ordinances/code_of_ordinances
            '16410' => 2597, // https://library.municode.com/ca/imperial_county/ordinances/code_of_ordinances
            //            '10418' => 20708, // https://library.municode.com/ca/azusa/ordinances/code_of_ordinances
            //            '16689' => 88915, // https://library.municode.com/ca/westlake_village/ordinances/code_of_ordinances
            //            '11307' => 88913, // https://library.municode.com/ca/tustin/ordinances/code_of_ordinances
            //            '16034' => 20984, // https://library.municode.com/ca/chino_hills/ordinances/code_of_ordinances
            //            '16516' => 69181, // https://library.municode.com/ca/national_city/ordinances/code_of_ordinances
            '14367' => 69217, // https://library.municode.com/ca/san_jose/ordinances/code_of_ordinances
            //            '16629' => 69259, // https://library.municode.com/ca/simi_valley/ordinances/code_of_ordinances
            //            '16597' => 69089, // https://library.municode.com/ca/salinas/ordinances/code_of_ordinances
            //            '16050' => 20676, // https://library.municode.com/ca/rocklin/ordinances/code_of_ordinances
            '16027' => 2569, // https://library.municode.com/ar/pulaski_county/ordinances/code_of_ordinances
            '13387' => 24832, // https://library.municode.com/ar/maumelle/ordinances/code_of_ordinances
            '13865' => 68858, // https://library.municode.com/wi/neenah/ordinances/code_of_ordinances
            '16602' => 5192, // https://library.municode.com/ut/salt_lake_county/ordinances/code_of_ordinances
            '41106' => 5203, // https://library.municode.com/ut/weber_county/ordinances/code_of_ordinances
            '10344' => 4715, // https://library.municode.com/sc/aiken_county/ordinances/code_of_ordinances
            '10724' => 4715, // https://library.municode.com/sc/aiken/ordinances/code_of_ordinances
            '19956' => 12304, // https://library.municode.com/tx/carrollton/ordinances/code_of_ordinances
            '10816' => 2845, // https://library.municode.com/ga/fulton_county/ordinances/code_of_ordinances
            '13805' => 3009, // https://library.municode.com/il/cook_county/ordinances/code_of_ordinances
            '12174' => 44371, // https://library.municode.com/fl/jacksonville/ordinances/code_of_ordinances
            '10132' => 44417, // https://library.municode.com/fl/tampa/ordinances/code_of_ordinances
            '12016' => 3144, // https://library.municode.com/in/indianapolis_-_marion_county/ordinances/code_of_ordinances
            '13981' => 59408, // https://library.municode.com/mi/ypsilanti/ordinances/code_of_ordinances
            '10156' => 98264, // https://library.municode.com/mo/kansas_city/ordinances/code_of_ordinances
            '16214' => 4147, // https://library.municode.com/nv/clark_county/ordinances/code_of_ordinances
            '14787' => 98262, // https://library.municode.com/nv/las_vegas/ordinances/code_of_ordinances
            '16028' => 66380, // https://library.municode.com/oh/akron/ordinances/code_of_ordinances
            '19996' => 33868, // https://library.municode.com/oh/cincinnati/ordinances/code_of_ordinances
            '16219' => 64889, // https://library.municode.com/oh/columbus/ordinances/code_of_ordinances
            '13723' => 65128, // https://library.municode.com/oh/dayton/ordinances/code_of_ordinances
            '10121' => 77339, // https://library.municode.com/va/norfolk/ordinances/code_of_ordinances
            '10257' => 69328, // https://library.municode.com/co/denver/ordinances/code_of_ordinances
            '11490' => 60334, // https://library.municode.com/mn/minneapolis/ordinances/code_of_ordinances
            '10061' => 61462, // https://library.municode.com/mn/st._paul/ordinances/code_of_ordinances
            '11926' => 3594, // https://library.municode.com/md/baltimore_county/ordinances/code_of_ordinances
            '15458' => 31218, // https://library.municode.com/az/tempe/ordinances/city_code
            '18013' => 69324, // https://library.municode.com/co/broomfield/ordinances/municipal_code
            '11804' => 2926, // https://library.municode.com/ga/troup_county/codes/code_of_ordinances
            '11265' => 111807, // https://library.municode.com/al/mobile/codes/code_of_ordinances
            '11385' => 73784, // https://library.municode.com/ky/owensboro/codes/code_of_ordinances
            '10907' => 49251, // https://library.municode.com/nc/hendersonville/codes/code_of_ordinances
            '14632' => 44967, // https://library.municode.com/GA/Roswell/codes/code_of_ordinances
            '12776' => 4299, // https://library.municode.com/nc/buncombe_county/codes/code_of_ordinances
            '15367' => 173447, // https://library.municode.com/nc/fuquay-varina/codes/code_of_ordinances
            '15701' => 60342, // https://library.municode.com/mn/plymouth/codes/code_of_ordinances
            '15875' => 71751, // https://library.municode.com/or/wilsonville/codes/code_of_ordinances
            '16248' => 69207, // https://library.municode.com/ca/carpinteria/codes/code_of_ordinances
            '18039' => 172242, // https://library.municode.com/co/centennial/codes/municipal_code
            '16509' => 173450, // https://library.municode.com/il/mundelein/codes/code_of_ordinances
            '15095' => 2593, // https://library.municode.com/ca/el_dorado_county/codes/code_of_ordinances
            '16610' => 69070, // https://library.municode.com/ca/san_rafael/codes/code_of_ordinances
            '16308' => 68915, // https://library.municode.com/ca/oakland/codes/code_of_ordinances
            '16579' => 68949, // https://library.municode.com/ca/richmond/codes/code_of_ordinances
            '16460' => 69216, // https://library.municode.com/ca/los_altos/codes/code_of_ordinances
            '16435' => 186508, // https://library.municode.com/ca/lafayette/codes/code_of_ordinances
            '15557' => 186509, // https://library.municode.com/ca/del_mar/codes/municipal_code
            '16242' => 69208, // https://library.municode.com/ca/campbell/codes/code_of_ordinances
            '11002' => 52197, // https://library.municode.com/al/hoover/ordinances/code_of_ordinances
            '10763' => 44116, // https://library.municode.com/ar/fayetteville/ordinances/code_of_ordinances
            '14680' => 3604, // https://library.municode.com/md/howard_county/ordinances/code_of_ordinances
            '11782' => 59386, // https://library.municode.com/mi/ann_arbor/ordinances/code_of_ordinances
            '13170' => 59047, // https://library.municode.com/mi/rochester_hills/ordinances/code_of_ordinances
            '11507' => 59060, // https://library.municode.com/mi/west_bloomfield/ordinances/code_of_ordinances
            '13032' => 3711, // https://library.municode.com/mi/wayne_county/ordinances/code_of_ordinances
            '10649' => 59418, // https://library.municode.com/mi/detroit/ordinances/code_of_ordinances
            '10945' => 62470, // https://library.municode.com/ne/omaha/ordinances/code_of_ordinances
            '14896' => 69526, // https://library.municode.com/nv/henderson/ordinances/code_of_ordinances
            '15498' => 4160, // https://library.municode.com/nv/washoe_county/ordinances/code_of_ordinances
            '15375' => 64911, // https://library.municode.com/oh/upper_arlington/ordinances/code_of_ordinances
            '13857' => 73388, // https://library.municode.com/wa/seattle/ordinances/municipal_code
            '16720' => 5384, // https://library.municode.com/wa/thurston_county/ordinances/code_of_ordinances
            // '12598' => 5485, // https://library.municode.com/wi/milwaukee_county/ordinances/code_of_ordinances
            '16684' => 67992, // https://library.municode.com/wi/wauwatosa/ordinances/code_of_ordinances
            '50000' => 67248, // https://library.municode.com/wi/madison/ordinances/code_of_ordinances
            '16574' => 69197, // https://library.municode.com/ca/redwood_city/ordinances/city_code
            '11910' => 111808, // https://library.municode.com/ms/corinth/ordinances/code_of_ordinances
            // '18039' => 172242, // https://library.municode.com/co/centennial/ordinances/municipal_code - Duplicate
            '18113' => 69334, // https://library.municode.com/co/basalt/ordinances/municipal_code
            '18020' => 69395, // https://library.municode.com/co/boulder/ordinances/municipal_code
            '14590' => 69392, // https://library.municode.com/co/longmont/ordinances/code_of_ordinances
            '18088' => 69394, // https://library.municode.com/co/superior/ordinances/municipal_code
            // '10376' => 44962, // https://library.municode.com/ga/atlanta/ordinances/code_of_ordinances - Duplicate
            '12100' => 44970, // https://library.municode.com/ga/alpharetta/ordinances/code_of_ordinances
            '14670' => 2863, // https://library.municode.com/ga/jackson_county/ordinances/code_of_ordinances
            '14385' => 44960, // https://library.municode.com/ga/sandy_springs/ordinances/code_of_ordinances
            '10637' => 2829, // https://library.municode.com/ga/dekalb_county/ordinances/code_of_ordinances
            '10572' => 2818, // https://library.municode.com/ga/cobb_county/ordinances/code_of_ordinances
            '12813' => 44850, // https://library.municode.com/ga/kennesaw/ordinances/code_of_ordinances
            '12110' => 44888, // https://library.municode.com/ga/decatur/ordinances/code_of_ordinances
            '12224' => 44851, // https://library.municode.com/ga/marietta/ordinances/code_of_ordinances
            '16289' => 2949, // https://library.municode.com/hi/county_of_maui/ordinances/code_of_ordinances
            '13492' => 3124, // https://library.municode.com/in/hamilton_county/ordinances/code_of_ordinances
            '17131' => 3166, // https://library.municode.com/in/st._joseph_county/ordinances/code_of_ordinances
            // '11490' => 60334, // https://library.municode.com/mn/minneapolis/ordinances/code_of_ordinances - Duplicate
            '15157' => 60320, // https://library.municode.com/mn/edina/ordinances/code_of_ordinances
            '14365' => 62109, // https://library.municode.com/mn/woodbury/ordinances/code_of_ordinances
            '14983' => 4356, // https://library.municode.com/nc/orange_county/ordinances/code_of_ordinances
            '19952' => 49488, // https://library.municode.com/nc/chapel_hill/ordinances/code_of_ordinances
            '10312' => 49808, // https://library.municode.com/nc/raleigh/ordinances/code_of_ordinances
            '19970' => 49417, // https://library.municode.com/nc/charlotte/ordinances/code_of_ordinances
            '12650' => 4320, // https://library.municode.com/nc/durham_county/ordinances/code_of_ordinances
            '19967' => 49086, // https://library.municode.com/nc/durham/ordinances/code_of_ordinances
            '16889' => 74413, // https://library.municode.com/ma/cambridge/ordinances/code_of_ordinances
            '13043' => 74431, // https://library.municode.com/ma/medford/ordinances/code_of_ordinances
            '11580' => 74440, // https://library.municode.com/ma/somerville/ordinances/code_of_ordinances
            '12962' => 191888, // https://library.municode.com/al/huntsville/ordinances/code_of_ordinances
            '14276' => 191890, // https://library.municode.com/al/mountain_brook/ordinances/code_of_ordinances
            '10158' => 191892, // https://library.municode.com/az/chandler/ordinances/code_of_ordinances
            '10822' => 191893, // https://library.municode.com/md/gaithersburg/ordinances/code_of_ordinances
            '14917' => 191894, // https://library.municode.com/md/rockville/ordinances/code_of_ordinances
            '12209' => 191895, // https://library.municode.com/md/chevy_chase/ordinances/code_of_ordinances
            '14755' => 191898, // https://library.municode.com/mt/bozeman/ordinances/code_of_ordinances
            '14160' => 191899, // https://library.municode.com/nv/reno/ordinances/administrative_code
        ];

        // using this in tinker to get locations:
        // $arr = [
        //     'ca/orange_county',
        //     'ca/los_angeles_county',
        //     'ca/alameda_county',
        // ];
        // foreach ($arr as $i) {
        //     [$state, $name] = explode('/', $i);
        //     $name = Str::title(str_replace(['_'], ' ', $name));
        //     $name = Str::title(str_replace([' County'], '', $name));
        //     $stateL = Location::where('slug', 'us-' . $state)->first();

        //     $locations = Location::whereHas('ancestors', fn ($q) => $q->whereKey($stateL->id))->nameMatches($name)->get();
        //     if ($locations->isEmpty()) {
        //         echo "No results found!!!!!!!" . PHP_EOL;
        //         echo $i . PHP_EOL;
        //         continue;
        //     }
        //     if (str_contains($i, 'county')) {
        //         $l = $locations->first();
        //     } else {
        //         $l = $locations->last();
        //     }
        //     echo $l->id . ',' . PHP_EOL;
        // }
    }

    // /**
    //  * Just a little helper utility to get the productId given the client name in the format state-abbrev/name (e.g. ca/sonoma_county).
    //  *
    //  * @codeCoverageIgnore
    //  *
    //  * @param string $name
    //  *
    //  * @return int|null
    //  */
    // public function getOrdinancesProductIdFromClientName(string $name): int|null
    // {
    //     [$stateAbbr, $clientName] = explode('/', $name);
    //     $clientName = str_replace('_', ' ', $clientName);
    //     $response = Http::get('https://api.municode.com/Clients/name?clientName=' . urlencode($clientName) . '&stateAbbr=' . $stateAbbr);

    //     $r = $response->json();
    //     $clientId = $r['ClientID'] ?? null;
    //     if (!$clientId) {
    //         return null;
    //     }
    //     $response = Http::get('https://api.municode.com/Products/name?clientId=' . $clientId . '&productName=code+of+ordinances');
    //     $r = $response->json();

    //     return $r['ProductID'] ?? null;
    // }
}
