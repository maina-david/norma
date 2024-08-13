<?php

namespace App\Services\Arachno\Crawlers\Sources\Za;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Link;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Geonames\Location;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\TocItemDraft;
use App\Stores\Corpus\ContentResourceStore;
use ArrayIterator;
use DOMElement;
use Generator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 */
class SouthAfricaSabinetLegislation extends AbstractSouthAfricaSabinet
{
    protected string $endPoint = 'https://api.sabinet-arq.com';

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'za-sabinet-legislation',
            'throttle_requests' => 300,
            'start_urls' => $this->getSabinetStartUrls(),
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/api\.sabinet-arq\.com\/search\/resourcespublic/')) {
            $this->handleCatalogueIndex($pageCrawl);
        }
        if ($this->arachno->crawlIsWorkChangesDiscovery($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/api\.sabinet-arq\.com\/search\/resourcespublic/')) {
            $this->handleSabinetChanges($pageCrawl);
        }
        if ($this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/api\.sabinet-arq\.com\/search\/resourcespublic/')) {
            $this->handleCatalogueNewWorks($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/api\.sabinet-arq\.com\/documents\/[0-9]+$/')) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/api\.sabinet-arq\.com\/documents\/[0-9]+\/htmlfulltext/')) {
            $this->handleCaptureDocument($pageCrawl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/api\.sabinet\-arq\.com/')) {
            $settings = [
                'options' => [
                    'headers' => $this->getHttpHeaders(),
                ],
            ];

            $url = $pageCrawl->pageUrl->url;
            if (str_contains($url, '/search/resourcespublic')) {
                $settings = $this->getDefaultPostData();

                if (str_contains($url, '?provincial')) {
                    $settings['options']['json']['query']['bool']['must'][0]['term']['product_types.keyword'] = 'Legal - Provincial Legislation';
                    $settings['options']['json']['_source']['includes'] = ['title', 'long_title', 'last_updated_date'];
                }
                if (str_contains($url, '?municipal')) {
                    $settings['options']['json']['query']['bool']['must'][0]['term']['product_types.keyword'] = 'Legal - Municipal By‑laws';
                    $settings['options']['json']['_source']['includes'] = ['title', 'long_title', 'municipality', 'last_updated_date'];
                }

                if (str_contains($url, '_changes')) {
                    $settings['options']['json']['_source']['includes'] = ['last_updated_date'];
                }
            }

            $pageCrawl->setHttpSettings($settings);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultPostData(): array
    {
        // API For Sabinet Municipality
        // API: "https://api.sabinet-arq.com/search/resourcespublic"
        // Method: POST
        // Query:
        // {
        //     "aggs": {
        //         "municipality": {
        //             "terms": {
        //                 "field": "municipality.keyword",
        //                 "size": 10000
        //             }
        //         }
        //     },
        //     "size": 0
        // }
        return [
            'method' => 'POST',
            'options' => [
                'headers' => $this->getHttpHeaders(),
                'json' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'product_types.keyword' => 'Legal - National Legislation (NetLaw)',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'from' => 0,
                    'size' => 9500,
                    '_source' => [
                        'includes' => ['main_act_label', 'title', 'long_title', 'last_updated_date'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueIndex(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $arr = $json['hits']['hits'] ?? [];
        $mainUrl = $pageCrawl->getFinalRedirectedUrl();
        foreach ($arr as $item) {
            $this->handleCreateCatalogueDocSabinet($pageCrawl, $item, $mainUrl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueNewWorks(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $arr = $json['hits']['hits'] ?? [];
        /** @var Collection<CatalogueDoc> */
        $docs = (new CatalogueDoc())->newCollection();
        $ids = [];
        foreach ($arr as $item) {
            $ids[] = $item['_id'];
        }
        $docs = CatalogueDoc::where('source_id', $pageCrawl->pageUrl->crawler?->source_id ?? 1)
            ->whereIn('source_unique_id', $ids)
            ->get()
            ->keyBy('source_unique_id');
        $mainUrl = $pageCrawl->getFinalRedirectedUrl();
        foreach ($arr as $item) {
            $id = $item['_id'];
            if (!isset($docs[$id])) {
                $this->handleCreateCatalogueDocSabinet($pageCrawl, $item, $mainUrl);
            }
        }
    }

    /**
     * @param PageCrawl           $pageCrawl
     * @param array<string,mixed> $item
     * @param string              $mainUrl
     *
     * @return void
     */
    protected function handleCreateCatalogueDocSabinet(PageCrawl $pageCrawl, array $item, string $mainUrl): void
    {
        $id = $item['_id'];
        $item = $item['_source'];
        $url = 'https://api.sabinet-arq.com/documents/' . $id;
        $viewUrl = 'https://discover.sabinet.co.za/document/' . $id;
        $summary = $item['long_title'] ?? null;
        $primaryLocationId = null;
        $muniTitle = '';

        $components = [];
        if (isset($item['main_act_label'])) {
            $components[] = $item['main_act_label'];
        }
        if (isset($item['municipality'])) {
            $components[] = $item['municipality'][0];
            $muniTitle = $item['municipality'][0];
            if (str_contains($muniTitle, '> ') && trim($muniTitle) !== 'Mpumalanga > Emalahleni Local Municipality') {
                $muniTitle = (string) Str::of($muniTitle)->afterLast('> ');
            } else {
                $muniTitle = $muniTitle;
            }
            $primaryLocationId = Location::where('location_country_id', 4)->where('title', $muniTitle)->first();
            $primaryLocationId = $primaryLocationId?->id;
        }
        if (isset($item['title'])) {
            $components[] = $item['title'];
        } elseif (isset($item['by_law_title'])) {
            $components[] = $item['by_law_title'];
        }

        $title = implode(' - ', $components);

        $catDoc = new CatalogueDoc([
            'title' => $title,
            'start_url' => $url,
            'view_url' => $viewUrl,
            'source_unique_id' => $id,
            'language_code' => 'eng',
            'summary' => $summary,
            'primary_location_id' => $primaryLocationId === null ? $this->getMunicipalLocation($muniTitle) : $primaryLocationId,
            'last_updated_at' => $item['last_updated_date'] ?? null,
        ]);
        $catDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        if (str_contains($mainUrl, '?national')) {
            $this->arachno->addCategoriesToCatalogueDoc($catDoc, ['Legal - National Legislation (NetLaw)']);
        } elseif (str_contains($mainUrl, '?provincial')) {
            $this->arachno->addCategoriesToCatalogueDoc($catDoc, ['Legal - Provincial Legislation']);
        } elseif (str_contains($mainUrl, '?municipal')) {
            $this->arachno->addCategoriesToCatalogueDoc($catDoc, ['Legal - Municipal By-laws']);
        }
    }

    /**
     * @param string $title
     *
     * @return int
     */
    public function getProvincialLocation($title): int
    {
        return match (strtolower($title)) {
            'eastern cape' => 41,
            'free state', 'orange free state' => 42,
            'gauteng', 'transvaal' => 43,
            'kwazulu-natal', 'natal' => 44,
            'limpopo' => 45,
            'mpumalanga' => 46,
            'north west' => 47,
            'northern cape' => 48,
            'western cape', 'cape of good hope' => 50,
            default => 4,
        };
    }

    /**
     * @param string $title
     *
     * @return int
     */
    public function getMunicipalLocation($title): int
    {
        return match ($title) {
            'Mpumalanga > Emalahleni Local Municipality' => 278,
            'Emalahleni Local Municipality' => 146,
            'City of uMhlathuze Local Municipality' => 232,
            'City of Ekurhuleni Metropolitan Municipality' => 72,
            'City of Mbombela Local Municipality' => 265,
            'Koukamma Local Municipality' => 141,
            'Fetakgomo Tubatse Local Municipality' => 252,
            'Chief Albert Luthuli Local Municipality' => 269,
            'Winnie Madikizela-Mandela Local Municipality' => 126,
            'Nquthu Local Municipality' => 363,
            'Garden Route District Municipality' => 106,
            'Modimolle-Mookgophong Local Municipality' => 262,
            'Ingquza Hill Local Municipality' => 156,
            'uMsinga Local Municipality' => 220,
            'Dr AB Xuma Local Municipality' => 147,
            default => 4,
        };
    }

    // USE TO SEARCH FOR MUNICIPAL PRIMARY LOCATIONS FROM THE DB
    //  /**
    //  * @param PageCrawl $pageCrawl
    //  *
    //  * @return void
    //  */
    // public function handleTitles(PageCrawl $pageCrawl): void
    // {
    //     $data = [
    //             "Limpopo > Makhado Local Municipality",
    //             "eThekwini Metropolitan Municipality",
    //             "Gauteng > City of Tshwane Metropolitan Municipality",
    //             "Western Cape > Saldanha Bay Local Municipality",
    //             "Eastern Cape > Dr Beyers Naude Local Municipality",
    //             "Western Cape > City of Cape Town Metropolitan Municipality",
    //             "Free State > Mangaung Metropolitan Municipality",
    //             "Mpumalanga > Thaba Chweu Local Municipality",
    //             "KwaZulu-Natal > Dr Nkosazana Dlamini Zuma Local Municipality",
    //             "North West > JB Marks Local Municipality",
    //             "Gauteng > City of Ekurhuleni Metropolitan Municipality",
    //             "Mpumalanga > City of Mbombela Local Municipality",
    //             "Western Cape > Cederberg Local Municipality",
    //             "Eastern Cape > King Sabata Dalindyebo Local Municipality",
    //             "Gauteng > City of Johannesburg Metropolitan Municipality",
    //             "KwaZulu-Natal > eThekwini Metropolitan Municipality",
    //             "Eastern Cape > Port St Johns Local Municipality",
    //             "George Local Municipality",
    //             "Western Cape > Hessequa Local Municipality",
    //             "Western Cape > Swartland Local Municipality",
    //             "Eastern Cape > Elundini Local Municipality",
    //             "Free State > Masilonyana Local Municipality",
    //             "North West > City of Matlosana Local Municipality",
    //             "Western Cape > Mossel Bay Local Municipality",
    //             "Western Cape > Knysna Local Municipality",
    //             "Western Cape > Stellenbosch Local Municipality",
    //             "KwaZulu-Natal > City of uMhlathuze Local Municipality",
    //             "KwaZulu-Natal > Greater Kokstad Local Municipality",
    //             "KwaZulu-Natal > KwaDukuza Local Municipality",
    //             "Western Cape > Drakenstein Local Municipality",
    //             "Eastern Cape > Winnie Madikizela-Mandela Local Municipality",
    //             "Mpumalanga > Steve Tshwete Local Municipality",
    //             "Eastern Cape > Buffalo City Metropolitan Municipality",
    //             "KwaZulu-Natal > Ulundi Local Municipality",
    //             "Mpumalanga > Emalahleni Local Municipality",
    //             "North West > Rustenburg Local Municipality",
    //             "Eastern Cape > Ntabankulu Local Municipality",
    //             "Free State > Dihlabeng Local Municipality",
    //             "Western Cape > Theewaterskloof Local Municipality",
    //             "Eastern Cape > Senqu Local Municipality",
    //             "KwaZulu-Natal > uMlalazi Local Municipality",
    //             "Eastern Cape > Enoch Mgijima Local Municipality",
    //             "KwaZulu-Natal > Ubuhlebezwe Local Municipality",
    //             "Limpopo > Greater Tzaneen Local Municipality",
    //             "Northern Cape > Ubuntu Local Municipality",
    //             "Western Cape > Oudtshoorn Local Municipality",
    //             "Eastern Cape > Mnquma Local Municipality",
    //             "Gauteng > Mogale City Local Municipality",
    //             "Western Cape > Bergrivier Local Municipality",
    //             "Western Cape > Langeberg Local Municipality",
    //             "Western Cape > Swellendam Local Municipality",
    //             "Gauteng > Emfuleni Local Municipality",
    //             "Limpopo > Ba-Phalaborwa Local Municipality",
    //             "Mpumalanga > Govan Mbeki Local Municipality",
    //             "Western Cape > Breede Valley Local Municipality",
    //             "Mpumalanga > Bushbuckridge Local Municipality",
    //             "Northern Cape > Ga-Segonyana Local Municipality",
    //             "Western Cape > Bitou Local Municipality",
    //             "Western Cape > Witzenberg Local Municipality",
    //             "Eastern Cape > Ndlambe Local Municipality",
    //             "Gauteng > Rand West City Local Municipality",
    //             "KwaZulu-Natal > Inkosi Langalibalele Local Municipality",
    //             "KwaZulu-Natal > Nquthu Local Municipality",
    //             "KwaZulu-Natal > Ray Nkonyeni Local Municipality",
    //             "Limpopo > Blouberg Local Municipality",
    //             "North West > Madibeng Local Municipality",
    //             "Northern Cape > Dawid Kruiper Local Municipality",
    //             "Eastern Cape > Kouga Local Municipality",
    //             "Eastern Cape > Makana Local Municipality",
    //             "Eastern Cape > Matatiele Local Municipality",
    //             "Free State > Moqhaka Local Municipality",
    //             "KwaZulu-Natal > Okhahlamba Local Municipality",
    //             "Western Cape > Overstrand Local Municipality",
    //             "Eastern Cape > Mbhashe Local Municipality",
    //             "Eastern Cape > Walter Sisulu Local Municipality",
    //             "KwaZulu-Natal > Alfred Duma Local Municipality",
    //             "KwaZulu-Natal > Endumeni Local Municipality",
    //             "Limpopo > Greater Giyani Local Municipality",
    //             "Limpopo > Molemole Local Municipality",
    //             "Northern Cape > Kai !Garib Local Municipality",
    //             "Northern Cape > Phokwane Local Municipality",
    //             "Northern Cape > Sol Plaatje Local Municipality",
    //             "Western Cape > Prince Albert Local Municipality",
    //             "Eastern Cape > Dr AB Xuma Local Municipality",
    //             "KwaZulu-Natal > Mandeni Local Municipality",
    //             "KwaZulu-Natal > Msunduzi Local Municipality",
    //             "KwaZulu-Natal > Richmond Local Municipality",
    //             "KwaZulu-Natal > uMzimkhulu Local Municipality",
    //             "Limpopo > Polokwane Local Municipality",
    //             "Mpumalanga > Lekwa Local Municipality",
    //             "Northern Cape > Nama Khoi Local Municipality",
    //             "Northern Cape > Thembelihle Local Municipality",
    //             "Northern Cape > Umsobomvu Local Municipality",
    //             "Eastern Cape > Nyandeni Local Municipality",
    //             "KwaZulu-Natal > Emadlangeni Local Municipality",
    //             "Northern Cape > Hantam Local Municipality",
    //             "Western Cape > Beaufort West Local Municipality",
    //             "KwaZulu-Natal > Abaqulusi Local Municipality",
    //             "KwaZulu-Natal > Newcastle Local Municipality",
    //             "Limpopo > Fetakgomo Tubatse Local Municipality",
    //             "Mpumalanga > Msukaligwa Local Municipality",
    //             "North West > Ramotshere Moiloa Local Municipality",
    //             "Eastern Cape > Blue Crane Route Local Municipality",
    //             "Eastern Cape > Sakhisizwe Local Municipality",
    //             "Eastern Cape > Umzimvubu Local Municipality",
    //             "KwaZulu-Natal > Mkhambathini Local Municipality",
    //             "Northern Cape > Emthanjeni Local Municipality",
    //             "Western Cape > Cape Agulhas Local Municipality",
    //             "Western Cape > Laingsburg Local Municipality",
    //             "Limpopo > Lephalale Local Municipality",
    //             "Mpumalanga > Thembisile Hani Local Municipality",
    //             "Northern Cape > Magareng Local Municipality",
    //             "Eastern Cape > Koukamma Local Municipality",
    //             "Eastern Cape > Sundays River Valley Local Municipality",
    //             "Free State > Nala Local Municipality",
    //             "Free State > Setsoto Local Municipality",
    //             "KwaZulu-Natal > Umdoni Local Municipality",
    //             "Limpopo > Greater Letaba Local Municipality",
    //             "Mpumalanga > Emakhazeni Local Municipality",
    //             "Mpumalanga > Nkomazi Local Municipality",
    //             "Northern Cape > John Taolo Gaetsewe District Municipality",
    //             "Northern Cape > Tsantsabane Local Municipality",
    //             "Eastern Cape > Nelson Mandela Bay Metropolitan Municipality",
    //             "Eastern Cape > Sarah Baartman District Municipality",
    //             "Free State > Mantsopa Local Municipality",
    //             "KwaZulu-Natal > Mthonjaneni Local Municipality",
    //             "Western Cape > Matzikama Local Municipality",
    //             "Free State > Kopanong Local Municipality",
    //             "Free State > Matjhabeng Local Municipality",
    //             "Gauteng > Midvaal Local Municipality",
    //             "KwaZulu-Natal > Umvoti Local Municipality",
    //             "Mpumalanga > Chief Albert Luthuli Local Municipality",
    //             "Mpumalanga > Victor Khanye Local Municipality",
    //             "Eastern Cape > Mhlontlo Local Municipality",
    //             "Eastern Cape > Raymond Mhlaba Local Municipality",
    //             "Free State > Maluti-a-Phofung Local Municipality",
    //             "Gauteng > Lesedi Local Municipality",
    //             "Gauteng > Merafong City Local Municipality",
    //             "Limpopo > Musina Local Municipality",
    //             "North West > Moses Kotane Local Municipality",
    //             "Eastern Cape > Emalahleni Local Municipality",
    //             "KwaZulu-Natal > uMngeni Local Municipality",
    //             "KwaZulu-Natal > uMshwathi Local Municipality",
    //             "Limpopo > Lepelle-Nkumpi Local Municipality",
    //             "Limpopo > Mogalakwena Local Municipality",
    //             "Mpumalanga > Dr JS Moroka Local Municipality",
    //             "North West > Lekwa-Teemane Local Municipality",
    //             "North West > Mahikeng Local Municipality",
    //             "Western Cape > Kannaland Local Municipality",
    //             "Northern Cape > Kgatelopele Local Municipality",
    //             "Northern Cape > Richtersveld Local Municipality",
    //             "Free State > Mohokare Local Municipality",
    //             "Free State > Tswelopele Local Municipality",
    //             "Northern Cape > Dikgatlong Local Municipality",
    //             "Northern Cape > Siyancuma Local Municipality",
    //             "KwaZulu-Natal > Mpofana Local Municipality",
    //             "Limpopo > Collins Chabane Local Municipality",
    //             "Limpopo > Thabazimbi Local Municipality",
    //             "Limpopo > Thulamela Local Municipality",
    //             "Matjhabeng Local Municipality",
    //             "Mpumalanga > Dr Pixley Ka Isaka Seme Local Municipality",
    //             "Eastern Cape > Ingquza Hill Local Municipality",
    //             "Limpopo > Ephraim Mogale Local Municipality",
    //             "Limpopo > Maruleng Local Municipality",
    //             "Limpopo > Modimolle-Mookgophong Local Municipality",
    //             "Western Cape > George Local Municipality",
    //             "uMzimkhulu Local Municipality",
    //             "Cederberg Local Municipality",
    //             "Free State > Nketoana Local Municipality",
    //             "KwaZulu-Natal > Big Five Hlabisa Local Municipality",
    //             "Limpopo > Bela-Bela Local Municipality",
    //             "Mossel Bay Local Municipality",
    //             "Mpumalanga > Mkhondo Local Municipality",
    //             "Free State > Phumelela Local Municipality",
    //             "North West > Greater Taung Local Municipality",
    //             "Northern Cape > Gamagara Local Municipality",
    //             "Northern Cape > Kamiesberg Local Municipality",
    //             "Kannaland Local Municipality",
    //             "KwaZulu-Natal > Dannhauser Local Municipality",
    //             "KwaZulu-Natal > King Cetshwayo District Municipality",
    //             "Langeberg Local Municipality",
    //             "Mpumalanga > Dipaleseng Local Municipality",
    //             "Eastern Cape > Amahlathi Local Municipality",
    //             "Eastern Cape > Intsika Yethu Local Municipality",
    //             "Eastern Cape > Inxuba Yethemba Local Municipality",
    //             "Fetakgomo Tubatse Local Municipality",
    //             "Free State > Letsemeng Local Municipality",
    //             "Free State > Ngwathe Local Municipality",
    //             "KwaZulu-Natal > Jozini Local Municipality",
    //             "Limpopo > Mopani District Municipality",
    //             "Mpumalanga > Nkangala District Municipality",
    //             "North West > Maquassi Hills Local Municipality",
    //             "North West > Naledi Local Municipality",
    //             "North West > Ngaka Modiri Molema District Municipality",
    //             "Oudtshoorn Local Municipality",
    //             "Eastern Cape > Amathole District Municipality",
    //             "Free State > Metsimaholo Local Municipality",
    //             "Gauteng > West Rand District Municipality",
    //             "KwaZulu-Natal > Nongoma Local Municipality",
    //             "KwaZulu-Natal > Umuziwabantu Local Municipality",
    //             "Limpopo > Elias Motsoaledi Local Municipality",
    //             "Limpopo > Makhuduthamaga Local Municipality",
    //             "Matatiele Local Municipality",
    //             "Northern Cape > Frances Baard District Municipality",
    //             "Northern Cape > Karoo Hoogland Local Municipality",
    //             "Northern Cape > Siyathemba Local Municipality",
    //             "City of Tshwane Metropolitan Municipality",
    //             "Eastern Cape > Alfred Nzo District Municipality",
    //             "Emalahleni Local Municipality",
    //             "Jozini Local Municipality",
    //             "KwaZulu-Natal > Mtubatuba Local Municipality",
    //             "Laingsburg Local Municipality",
    //             "North West > Ditsobotla Local Municipality",
    //             "Nquthu Local Municipality",
    //             "City of Johannesburg Metropolitan Municipality",
    //             "Eastern Cape > Great Kei Local Municipality",
    //             "KwaZulu-Natal > Ugu District Municipality",
    //             "KwaZulu-Natal > Umzumbe Local Municipality",
    //             "KwaZulu-Natal > uMhlabuyalingana Local Municipality",
    //             "KwaZulu-Natal > uPhongolo Local Municipality",
    //             "Lekwa Local Municipality",
    //             "Madibeng Local Municipality",
    //             "Mkhondo Local Municipality",
    //             "Newcastle Local Municipality",
    //             "North West > Kagisano-Molopo Local Municipality",
    //             "North West > Moretele Local Municipality",
    //             "Northern Cape > !Kheis Local Municipality",
    //             "Northern Cape > Kareeberg Local Municipality",
    //             "Western Cape > Garden Route District Municipality",
    //             "Western Cape > West Coast District Municipality",
    //             "uMshwathi Local Municipality",
    //             "Bergrivier Local Municipality",
    //             "Chief Albert Luthuli Local Municipality",
    //             "Free State > Mafube Local Municipality",
    //             "KwaDukuza Local Municipality",
    //             "KwaZulu-Natal > Edumbe Local Municipality",
    //             "KwaZulu-Natal > Impendle Local Municipality",
    //             "KwaZulu-Natal > uMgungundlovu District Municipality",
    //             "Limpopo > Capricorn District Municipality",
    //             "Maphumulo Local Municipality",
    //             "Mpumalanga > Gert Sibande District Municipality",
    //             "North West > Bojanala Platinum District Municipality",
    //             "North West > Ratlou Local Municipality",
    //             "Western Cape > Overberg District Municipality",
    //             "Winnie Madikizela-Mandela Local Municipality",
    //             "Blouberg Local Municipality",
    //             "Blue Crane Route Local Municipality",
    //             "Drakenstein Local Municipality",
    //             "Free State > Xhariep District Municipality",
    //             "Govan Mbeki Local Municipality",
    //             "Great Kei Local Municipality",
    //             "Greater Taung Local Municipality",
    //             "Kagisano-Molopo Local Municipality",
    //             "KwaZulu-Natal > Harry Gwala District Municipality",
    //             "KwaZulu-Natal > Nkandla Local Municipality",
    //             "KwaZulu-Natal > uMfolozi Local Municipality",
    //             "Limpopo > Sekhukhune District Municipality",
    //             "Limpopo > Vhembe District Municipality",
    //             "Mogale City Local Municipality",
    //             "Mpumalanga > Ehlanzeni District Municipality",
    //             "Msukaligwa Local Municipality",
    //             "North West > Dr Kenneth Kaunda District Municipality",
    //             "North West > Mamusa Local Municipality",
    //             "North West > Tswaing Local Municipality",
    //             "Northern Cape > Khai-Ma Local Municipality",
    //             "Northern Cape > Namakwa District Municipality",
    //             "Thaba Chweu Local Municipality",
    //             "Umzimvubu Local Municipality",
    //             "Western Cape > Cape Winelands District Municipality",
    //             "Western Cape > Central Karoo District Municipality",
    //             "Amajuba District Municipality",
    //             "Bela-Bela Local Municipality",
    //             "Eastern Cape > Joe Gqabi District Municipality",
    //             "Eastern Cape > Ngqushwa Local Municipality",
    //             "Edumbe Local Municipality",
    //             "Free State > Tokologo Local Municipality",
    //             "King Sabata Dalindyebo Local Municipality",
    //             "KwaZulu-Natal > Ndwedwe Local Municipality",
    //             "KwaZulu-Natal > iLembe District Municipality",
    //             "KwaZulu-Natal > uMsinga Local Municipality",
    //             "Lekwa-Teemane Local Municipality",
    //             "Mnquma Local Municipality",
    //             "North West > Kgetlengrivier Local Municipality",
    //             "Northern Cape > Renosterberg Local Municipality",
    //             "Stellenbosch Local Municipality",
    //             "Walter Sisulu Local Municipality",
    //             "uMhlabuyalingana Local Municipality",
    //             "uMzinyathi District Municipality",
    //             "Bitou Local Municipality",
    //             "Buffalo City Metropolitan Municipality",
    //             "Capricorn District Municipality",
    //             "City of Cape Town Metropolitan Municipality",
    //             "City of uMhlathuze Local Municipality",
    //             "Dannhauser Local Municipality",
    //             "Dipaleseng Local Municipality",
    //             "Dr JS Moroka Local Municipality",
    //             "Dr Nkosazana Dlamini Zuma Local Municipality",
    //             "Eastern Cape > Chris Hani District Municipality",
    //             "Eastern Cape > OR Tambo District Municipality",
    //             "Free State > Thabo Mofutsanyana District Municipality",
    //             "Hessequa Local Municipality",
    //             "Inkosi Langalibalele Local Municipality",
    //             "Inxuba Yethemba Local Municipality",
    //             "Kgetlengrivier Local Municipality",
    //             "Kouga Local Municipality",
    //             "KwaZulu-Natal > Maphumulo Local Municipality",
    //             "KwaZulu-Natal > Zululand District Municipality",
    //             "KwaZulu-Natal > uThukela District Municipality",
    //             "Maruleng Local Municipality",
    //             "Mhlontlo Local Municipality",
    //             "Mkhambathini Local Municipality",
    //             "Mopani District Municipality",
    //             "Naledi Local Municipality",
    //             "Ndwedwe Local Municipality",
    //             "Nkandla Local Municipality",
    //             "Nkangala District Municipality",
    //             "Northern Cape > Joe Morolong Local Municipality",
    //             "Northern Cape > Pixley ka Seme District Municipality",
    //             "Northern Cape > ZF Mgcawu District Municipality",
    //             "Ntabankulu Local Municipality",
    //             "Prince Albert Local Municipality",
    //             "Ramotshere Moiloa Local Municipality",
    //             "Siyancuma Local Municipality",
    //             "Swartland Local Municipality",
    //             "Thabazimbi Local Municipality",
    //             "Theewaterskloof Local Municipality",
    //             "Tswaing Local Municipality",
    //             "Ubuhlebezwe Local Municipality",
    //             "Umsobomvu Local Municipality",
    //             "Umuziwabantu Local Municipality",
    //             "Waterberg District Municipality",
    //             "Witzenberg Local Municipality",
    //             "uMngeni Local Municipality",
    //             "uMsinga Local Municipality",
    //             "uPhongolo Local Municipality",
    //             "Chris Hani District Municipality",
    //             "City of Ekurhuleni Metropolitan Municipality",
    //             "Dr Beyers Naude Local Municipality",
    //             "Emfuleni Local Municipality",
    //             "Free State > Fezile Dabi District Municipality",
    //             "Gauteng > Sedibeng District Municipality",
    //             "KwaZulu-Natal > Amajuba District Municipality",
    //             "KwaZulu-Natal > uMkhanyakude District Municipality",
    //             "KwaZulu-Natal > uMzinyathi District Municipality",
    //             "Magareng Local Municipality",
    //             "Matzikama Local Municipality",
    //             "Mtubatuba Local Municipality",
    //             "Nelson Mandela Bay Metropolitan Municipality",
    //             "Overstrand Local Municipality",
    //             "Senqu Local Municipality",
    //             "Tswelopele Local Municipality",
    //             "Zululand District Municipality",
    //             "uMfolozi Local Municipality",
    //     ];

    //     foreach ($data as $title) {
    //         if(str_contains($title, '> ')){
    //             $title = Str::of($title)->afterLast('> ');
    //             $title = trim($title);
    //             $location = Location::where('location_country_id', 4)->where('title', $title)->first();
    //             $primary_location_id = $location?->id;
    //             echo $title . PHP_EOL;
    //             echo $primary_location_id . PHP_EOL;
    //         }
    //     }
    // }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleSabinetChanges(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $arr = $json['hits']['hits'] ?? [];
        /** @var Collection<CatalogueDoc> */
        $docs = (new CatalogueDoc())->newCollection();
        foreach ($arr as $item) {
            $id = $item['_id'];
            $item = $item['_source'];
            $docs->add(new CatalogueDoc([
                'source_unique_id' => $id,
                'last_updated_at' => $item['last_updated_date'] ?? null,
            ]));
        }
        $this->arachno->fetchChangedUpdatedDocs($docs, $pageCrawl->pageUrl->crawler?->source_id ?? 1);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $json = $pageCrawl->getJson();
        $data = $json['data'];
        $url = $pageCrawl->getFinalRedirectedUrl();
        $id = (string) Str::of($url)->after('/documents/');
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $title = $data['by_law_title']['value'] ?? $data['title']['value'];
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $title);
        $date = (string) Str::of($data['commencement_date']['value'] ?? '')->before(' ');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $date);
        if (isset($data['act_number'])) {
            $number = $data['act_number']['value'][0] ?? null;
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $number);
        }
        if (isset($data['long_title'])) {
            $longTitle = $data['long_title']['value'] ?? null;
            $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $longTitle);
        }
        if (isset($data['subject_s_'])) {
            $keywords = $data['subject_s_']['value'] ?? [];
            $this->arachno->setDocMetaProperty($pageCrawl, 'keywords', $keywords);
        }
        if (isset($data['municipality'])) {
            $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catalogueDoc->primary_location_id);
        } elseif (isset($data['province'])) {
            $province = $data['province']['value'][0] ?? null;
            $location = $this->getProvincialLocation(strtolower($province));
            $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $location);
        } else {
            $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '4');
        }
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', 'https://discover.sabinet.co.za/document/' . $id);

        $headers = $this->getHttpHeaders();
        $response = Http::withHeaders($headers)
            ->acceptJson()
            ->post($this->endPoint . '/search/resourcespublic', [
                'query' => [
                    'ids' => [
                        'values' => [$id],
                    ],
                ],
                'from' => 0,
                'size' => 1,
                '_source' => [
                    'includes' => ['*'],
                ],
            ]);
        $responseData = $response->json()['hits']['hits'] ?? [];
        $workType = $this->getWorkTypeByResourceType($responseData[0]['_source']['resource_type'] ?? '');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $this->arachno->followLink($pageCrawl, new UrlFrontierLink([
            'url' => $url . '/htmlfulltext',
            'http_settings' => [
                'options' => [
                    'headers' => $this->getHttpHeaders(),
                ],
            ],
        ]));
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCaptureDocument(PageCrawl $pageCrawl): void
    {
        $data = $pageCrawl->getJson()['data'];
        $fulltext = $data['fulltext'];
        //        $sections = $data['sections'];
        $url = $pageCrawl->getFinalRedirectedUrl();
        //        $this->arachno->captureTocFromGenerator($pageCrawl, $this->generateToCItems($sections, $url, 1));
        $fulltext = (string) Str::of($fulltext)->after('<div>')->beforeLast('</div>');

        $fulltext = '<html>' . $fulltext . '</html>';
        $dom = new DomCrawler($fulltext);

        /** @var ArrayIterator<int, DOMElement> $newSections */
        $newSections = $dom->filter('a[name], a[id]')->getIterator();
        $this->arachno->captureTocFromGenerator($pageCrawl, $this->generateToCItems($newSections, $url, 1));

        if ($dom->filter('a[href*="/downloadalt/"]')->count() > 0) {
            $contentResourceStore = app(ContentResourceStore::class);
            $headers = $this->getHttpHeaders();
            foreach ($dom->filter('a[href*="/downloadalt/"]') as $index => $link) {
                /** @var DOMElement $link */
                $href = str_replace('/downloadalt/', '', $link->getAttribute('href'));
                [$num1, $num2] = explode('/', $href);
                $url = 'https://api.sabinet-arq.com/documents/' . $num1 . '/alt/' . $num2;

                $response = Http::withHeaders($headers)
                    ->acceptJson()
                    ->get($url);
                $tempUrl = $response->json()['data']['path'] ?? null;
                if (!$tempUrl) {
                    continue;
                }

                $pdf = Http::get($tempUrl);
                $resource = $contentResourceStore->storeResource($pdf, 'application/pdf');
                $newHref = $contentResourceStore->getLinkForResource($resource);
                $link->setAttribute('href', $newHref);
            }
        }

        foreach ($dom->filter('a[name]') as $anchor) {
            /** @var DOMElement $anchor */
            $name = $anchor->getAttribute('name');
            $name = preg_match('/^\d+/', $name) ? "l{$name}" : $name;
            $anchor->setAttribute('id', str_replace(' ', '_', urldecode($name)));
        }

        /** @var DOMElement $irrelevantAnchor */
        foreach ($dom->filter('a[href*="#"]') as $irrelevantAnchor) {
            if (!str_starts_with($irrelevantAnchor->getAttribute('href'), '/content')) {
                $irrelevantAnchor->removeAttribute('href');
            }
        }

        $this->arachno->captureContent($pageCrawl, $dom->outerHtml());

        /** @var Link|null $link */
        $link = Link::where('uid', Link::hashForDB($pageCrawl->pageUrl->url))->first();

        /** @var \App\Models\Corpus\Doc $doc */
        $doc = $pageCrawl->getDoc();
        $doc->update(['start_link_id' => $link->id ?? $doc->start_link_id]);
    }

    /**
     * @param ArrayIterator<int, DOMElement> $sections
     * @param string                         $baseUrl
     * @param int                            $level
     *
     * @return Generator
     */
    public function generateToCItems(iterable $sections, string $baseUrl, int $level): Generator
    {
        foreach ($sections as $section) {
            $crawler = new DomCrawler($section);
            $sectionTextEl = $crawler->closest('p')?->getNode(0) ?? $crawler->closest('h1')?->getNode(0);

            if (!$sectionTextEl) {
                continue;
            }

            /** @var DOMElement $sectionTextEl */
            /** @var DOMElement $parent */
            $parent = $section->parentNode;
            $sectionText = $this->getUsableText($sectionTextEl) ?? $this->getUsableText($parent);

            if (!$sectionText && $section->getAttribute('name')) {
                $sectionText = $section->getAttribute('name');
            }

            if (!$sectionText) {
                continue;
            }

            $target = $section->getAttribute('name');
            $target = str_replace(' ', '_', urldecode($target));
            $target = empty($target) ? $section->getAttribute('id') : $target;

            $titleText = '';

            if (preg_match('/^(part|chapter|title|schedule)/', strtolower(trim($sectionText)))) {
                /** @var DOMElement|null $secParagraph */
                $secParagraph = $crawler->closest('p')?->getNode(0);

                if (!$secParagraph || !$sibling = $secParagraph->nextElementSibling) {
                    continue;
                }

                $secParaText = $this->getUsableTitle($sibling, 1);

                $titleText = !empty($secParaText) && strtoupper($secParaText) == $secParaText ? $secParaText : '';
            }

            $label = trim(strip_tags($sectionText)) . ' ' . $titleText;
            /** @var DOMElement $firstSpan */
            $firstSpan = $crawler->closest('p')?->filter('span')->getNode(0);
            $label = strlen($label) <= 150 ? $label : $firstSpan->textContent ?? Str::of($label)->before(' ');

            yield new TocItemDraft([
                'href' => $baseUrl . '#' . $target,
                //                'label' => strip_tags($section['label']),
                'label' => $label,
                'level' => $level,
                //                'source_unique_id' => trim($uniqueId),
                'source_unique_id' => $target,
            ]);
            //            if (is_array($section['content'])) {
            //                foreach ($this->generateToCItems($section['content'], $baseUrl, $level + 1) as $item) {
            //                    yield $item;
            //                }
            //            }
        }
    }

    protected function getUsableTitle(DOMElement $element, int $limit, int $current = 0): ?string
    {
        if ($text = $this->getUsableText($element)) {
            return $text;
        }

        if ($limit === $current || !$element->nextElementSibling) {
            return null;
        }

        return $this->getUsableTitle($element->nextElementSibling, $limit, $current + 1);
    }

    protected function getUsableText(DOMElement $element): ?string
    {
        return !empty($element->textContent) && htmlentities($element->textContent) !== '&nbsp;' ? $element->textContent : null;
    }

    protected function getWorkTypeByResourceType(string $resourceType): string
    {
        return match ($resourceType) {
            'National Act' => 'act',
            'National Regulation' => 'regulation',
            'Provincial Act' => 'act',
            'Provincial Regulation' => 'regulation',
            'Provincial Rules' => 'rule',
            //  U+2011 is used in Sabinet.... The character U+2011 "‑" could be confused with the character U+002d "-",
            // so adding both here
            'Municipal By‑laws','Municipal By-laws' => 'bylaw',
            default => 'act',
        };
    }

    /**
     * @return array<string, array<UrlFrontierLink>>
     */
    public function getSabinetStartUrls(): array
    {
        $searchApi = $this->endPoint . '/search/resourcespublic';

        return [
            'type_' . CrawlType::FULL_CATALOGUE->value => [
                new UrlFrontierLink(['url' => $searchApi . '?national']),
                new UrlFrontierLink(['url' => $searchApi . '?provincial']),
                new UrlFrontierLink(['url' => $searchApi . '?municipal']),
            ],
            'type_' . CrawlType::WORK_CHANGES_DISCOVERY->value => [
                new UrlFrontierLink(['url' => $searchApi . '?national_changes']),
                new UrlFrontierLink(['url' => $searchApi . '?provincial_changes']),
                new UrlFrontierLink(['url' => $searchApi . '?municipal_changes']),
            ],
            'type_' . CrawlType::NEW_CATALOGUE_WORK_DISCOVERY->value => [
                new UrlFrontierLink(['url' => $searchApi . '?national']),
                new UrlFrontierLink(['url' => $searchApi . '?provincial']),
                new UrlFrontierLink(['url' => $searchApi . '?municipal']),
            ],
        ];
    }
}
