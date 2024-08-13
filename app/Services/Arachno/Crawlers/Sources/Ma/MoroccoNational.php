<?php

namespace App\Services\Arachno\Crawlers\Sources\Ma;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

/**
 * @codeCoverageIgnore
 *
 * slug: ma-morocco-national
 * title: Morocco National
 * url: https://www.mem.gov.ma/en/Pages/TextesReglementaires.aspx
 */
class MoroccoNational extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ma-morocco-national',
            'throttle_requests' => 300,
            'start_urls' => $this->getMoroccoStartUrls(),
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/i')) {
                $this->handleMeta($pageCrawl);
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDocs(): array
    {
        return [
            '210/BO_7119_Ar' => [
                'title' => ' Joint order of the Minister of Energy Transition and Sustainable Development and Minister Delegate to the Minister of Economy and Finance in charge of the budget No. 1678.22 issued on Dhu al-kaada 23 1443 (June 23, 2022) Determining the costs for services',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/210/BO_7119_Ar.PDF',
                'summary' => 'The services provided by the NLEM are due for payment especially determinated in this joint order.',
                'date' => '2022',
            ],
            '209/BO_7022_Fr' => [
                'title' => 'The Order of the Minister of Energy, Mines and the Environment No. 1948-21 of 5 hija 1442 (July 16, 2021) on the characteristics of large petroleum products',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/209/BO_7022_Fr.pdf',
                'summary' => 'The major petroleum products specified below: Super Unleaded, Diesel 10 ppm, Fuel-Oil, LPG, must, when held for sale, offered for sale or sold after delivery for domestic consumption, be comply with the characteristics corresponding to their name. These characteristics set for each product its physical or chemical properties and in particular all or part of the following characteristics: color, viscosity, deposit by cooling, flash point, vapor pressure, combustion characteristics, pour point, limit temperature of filterability, acidity, corrosive and anti-corrosive properties, limit contents of various impurities such as water, sediments, sulphur, etc.',
                'date' => '2021',
            ],
            '168/Arr%C3%AAt%C3%A9%20DIV-SERV%20IG%202014' => [
                'title' => 'Order of the Minister of Energy, Mining, Water and Environment​ No. 1165.14 of 4 Jumada II 1435 (4 april 2014)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/168/Arr%C3%AAt%C3%A9%20DIV-SERV%20IG%202014.pdf',
                'summary' => 'Order of the Minister of Energy, Mining, Water and Environment ​No. 1165.14 of 4 Jumada II 1435 (4 april 2014) to determine the number of employees assigned to inspection tasks at the General Inspectorate of the  Minister of Energy, Mining, Water and Environment​​- Energy and Mining Sector​',
                'date' => '2014',
            ],
            '167/D%C3%A9cret%20IGM%202011' => [
                'title' => 'Decree No. 2.11.112 of 20 Rajeb 1432 (Jun 23, 2011)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/167/D%C3%A9cret%20IGM%202011.pdf',
                'summary' => '​Decree No. 2.11.112 of 20 Rajeb 1432 (Jun 23, 2011) adopted for general inspections of ministries B.O. n°5960.12 of 12 chaaban 1432 (Jun 14, 2011)',
                'date' => '2011',
            ],
            '164/Loi_33-13' => [
                'title' => 'Law number 33-1 3 of July 01, 2015 relating to mines',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/164/Loi_33-13.pdf',
                'summary' => 'The new Law on Mines enacted in July 2015 and which entered into force on May 23, 2016 repeals the old mining code that dates back to the 1950s of the last century ("Dahir" – Royal Decree of April 16, 1951) to take into account the developments in the mining sector on the international scene. The purpose of this law is to strengthen the contribution of this sector to the development of mineral exploration and research in order to discover new deposits in Morocco while ensuring the sustainable development of the national mining industry.',
                'date' => '2015',
            ],
            '163/Loi74-15(CADETAF)fr' => [
                'title' => ' “Dahir” (Royal decree) number 1-16-131 of 21 Kaada 1437 (August 25, 2016) promulgating the law n ° 74-15 relating to the region of Tafilalet and Figuig',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/163/Loi74-15(CADETAF)fr.pdf',
                'summary' => 'The artisanal mining activity was governed by the Dahir of December 01, 1960 establishing the mining region of Tafilalet and Figuig and which also created the Purchasing and Development Center of the mining region of Tafilalet and Figuig (CADETAF), a public institution endowed with legal personality and financial autonomy. The enactment of the new Law number 74-15 relating to the mining region of Tafilalet and Figuig aims at the restructuring of the activity in this zone in order to give attractiveness to this region which covers an area of 60,000 km2 and which contains promising mineral potential. This restructuring is taking place by fostering private initiative and providing social support for artisanal miners. The intervention of private operators can be made by means of partnership agreements with artisanal miners based on a compensation system bearing a fixed fee or "key money"" and royalties in case of discovery of a commercial deposit.',
                'date' => '2016',
            ],
            '162/Statut%20mineur' => [
                'title' => 'Recast of the “Dahir” (Royal decree) No. 1.60.007 of December 24, 1960 concerning the status of the personnel of the mining companies',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/162/Statut%20mineur.pdf',
                'summary' => 'Recently, the Department of Energy and Mining has withdrawn this draft text which has been under examination at the General Secretariat of the Government (SGG) with a view to enriching it with additional comments and suggestions from professionals and social partners, particularly after the enactment of the new law No. 33.13 on Mining. Currently, the Department is pursuing the finalization of the revision of the Diggers Statute by examining the comments and observations made by the various social partners. Also, a discussion of the project with the professionals is contemplated. The objective of this draft bill is to ensure a calm social climate and build healthy working relationships for the benefit of both workers and employers. It also institutes staff representative institutions and safety representatives in mining companies as well as vocational training.',
                'date' => '1960',
            ],
            '161/REGLEMENT%20GENERAL%20SUR%20L%E2%80%99EXPLOITATION' => [
                'title' => 'Overhaul of the general regulation on the exploitation of mines, except fuels (ministerial (“vizirial”) decrees of February 18th, 1938 and of July 4th, 1939)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/161/REGLEMENT%20GENERAL%20SUR%20L%E2%80%99EXPLOITATION.pdf',
                'summary' => '​The proposed legislation consists of adapting current mining regulations to the technological advances in the mining sector and giving a new dimension to workers health and safety issues. A draft text is being finalized in consultation with the mining profession through the formation of joint committees working on the various chapters of the decree. The main provisions relate to the following main points: work on site, ventilation, lighting, use of explosives and site hygiene.',
                'date' => '1938',
            ],
            '156/LAW2003-Anglais' => [
                'title' => 'Law No. 21-90 amended and supplemented by Law No. 27- 99',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/156/LAW2003-Anglais.pdf',
                'summary' => 'The new Law on Mines enacted in July 2015 and which entered into force on May 23, 2016 repeals the old mining code that dates back to the 1950s of the last century ("Dahir" – Royal Decree of April 16, 1951) to take into account the developments in the mining sector on the international scene. The purpose of this law is to strengthen the contribution of this sector to the development of mineral exploration and research in order to discover new deposits in Morocco while ensuring the sustainable development of the national mining industry.',
                'date' => '2003',
            ],
            // No PDF Link Available
            // 'DDD3E43339DDD9AB062587FF00713F6F' => [
            //     'title' => 'Law No. 21-90 amended and supplemented by Law No. 27- 99',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=53F80DDC11A73909062587FF006BC2B3&action=?openDocument',
            //     'summary' => 'This code contains very attractive incentives for oil exploration: exemptions from customs duties, from VAT, and from the Corporate Tax in the event of exploitation (10 consecutive years from the date of regular commissioning of any operating concession). For more efficiency in regulatory matters, the Department of Energy and Mining is currently reflecting on the revision of the Hydrocarbons Code.',
            //     'date' => '2015'
            // ],
            // No PDF Link Available
            // 'DDD3E43339DDD9AB062587FF00713F6F' => [
            //     'title' => 'DRAFT DECREE TAKEN FOR THE APPLICATION OF THE PROVISIONS OF ARTICLE 116 OF THE MINING LAW NO. 33-13',
            //     'start_url' => 'http://legislacion.asamblea.gob.ni/Normaweb.nsf/xpNorma.xsp?documentId=53F80DDC11A73909062587FF006BC2B3&action=?openDocument',
            //     'summary' => 'This draft decree is drawn up in accordance with the provisions of Article 116 of Law No. 33-13 on mines, enacted by "Dahir" (Royal decree)  No. 1.15.76 of 14 Ramadan 1436 (July 01, 2015). It includes various regulatory provisions to organize and govern the activities of extraction, collection and marketing of mineralogical specimens, fossils and meteorites, while addressing the challenges of the preservation of the national geological heritage.',
            //     'date' => '2015'
            // ],
            '151/Dahir%20portant%20loi%20n%C2%B0%201-72-255' => [
                'title' => '”Dahir” (Royal decree) establishing law n ° 1-72-255 of the 18 Muharram 1393 (February 22, 1973)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/151/Dahir%20portant%20loi%20n%C2%B0%201-72-255.pdf',
                'summary' => '“Dahir” (Royal decree) establishing law No. 1-72-255 of 18 Muharram 1393 (February 22, 1973)relating to the import, export, refining, filling , storage, and distribution of hydrocarbons',
                'date' => '1973',
            ],
            '150/D%C3%A9cret%20n%C2%B0%202-72-513%20' => [
                'title' => 'Decree No. 2-72-513 of 3 Rabii I 1393 (April 07, 1973)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/150/D%C3%A9cret%20n%C2%B0%202-72-513%20.pdf',
                'summary' => '​Decree No. 2-72-513 of 3 Rabii I 1393 (April 07, 1973) adopted for the implementation of the “Dahir” (Royal decree) Law No. 1-72-255 of 18 March 1393 (22 February 1973) on the importation, the export, refining in a refinery and in a filling center, the storage, and the distribution of hydrocarbons.',
                'date' => '1973',
            ],
            '149/Arr%C3%AAt%C3%A9%20n%C2%B0%201282-06' => [
                'title' => 'Order of the Minister of Energy and Mining No. 1282-06 of 4 Jumada II, 1427 (June 30, 2006)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/149/Arr%C3%AAt%C3%A9%20n%C2%B0%201282-06.pdf',
                'summary' => 'Order of the Minister of Energy and Mining No. 1282-06 of 4 Jumada II, 1427 (June 30, 2006) relating to the Retail Distribution Network of Refiners in Refineries for Hydrocarbon products other than Liquefied Petroleum Gases',
                'date' => '2006',
            ],
            "148/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l'artisanat,%20de%20l'industrie,%20des%20mines%20et%20de%20la%20marine%20marchande%20n%C2%B0%20345-68" => [
                'title' => 'Order of the Minister of Trade, Crafts, Industry, Mining, and Merchant Navy No. 345-68 of June 11, 1968',
                'start_url' => "https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/148/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l'artisanat,%20de%20l'industrie,%20des%20mines%20et%20de%20la%20marine%20marchande%20n%C2%B0%20345-68.pdf",
                'summary' => 'Order of the Minister of Trade, Crafts, Industry, Mining and Merchant Navy No. 345-68 of June 11, 1968 concerning the movement of petrol stations or filling stations',
                'date' => '1968',
            ],
            '147/Arr%C3%AAt%C3%A9%20%20n%C2%B0%201263-91%20du%209%20chaoual%201413%20(1er%20avril%201993)%20' => [
                'title' => 'Joint Order of the Minister of Energy and Mining, the Minister of Public Works, Vocational Training and Training of Executives and of the Minister of Transport No. 1263-91 of 9 Chaoual 1413 (April 1, 1993)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/147/Arr%C3%AAt%C3%A9%20%20n%C2%B0%201263-91%20du%209%20chaoual%201413%20(1er%20avril%201993)%20.pdf',
                'summary' => 'Joint Order of the Minister of Energy and Mining, the Minister of Public Works, Vocational Training and Training of Executives, and of the Minister of Transport No. 1263-91 of 9 Chaoual 1413 (1 April 1993) approving the Regulations relating to the safety standards applicable to filling plants, to bulk or cylinder deposits, and to fixed storage facilities for industrial or domestic use of liquefied petroleum gases, as well as to the packaging, handling, transport and use of such products.',
                'date' => '1993',
            ],
            "146/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l'industrie,%20des%20mines%20et%20de%20la%20marine%20marchande%20n%C2%B0%20773-73" => [
                'title' => 'Order of the Minister of Trade, Industry, Mining and Merchant Navy No. 773-73 of 19 January II 1393 (July 20, 1973)',
                'start_url' => "https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/146/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l'industrie,%20des%20mines%20et%20de%20la%20marine%20marchande%20n%C2%B0%20773-73.pdf",
                'summary' => 'Order of the Minister of Trade, Industry, Mining and Merchant Marine No. 773-73 of 19 Jumada II, 1393 (July 20, 1973) defining the importance of the fleet of cylinders in filling centers',
                'date' => '1973',
            ],
            '145/Loi%20n%C2%B0%20009-71%20' => [
                'title' => 'Law No. 009-71 of 21 Sha’ban 1391 (October 12, 1971)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/145/Loi%20n%C2%B0%20009-71%20.pdf',
                'summary' => '​Law No. 009-71 of 21 Sha’ban 1391 (October 12, 1971) on Safety Stocks',
                'date' => '1971',
            ],
            '144/D%C3%A9cret%20n%C2%B02-72-622' => [
                'title' => 'Decree No. 2-72-622 of 8 Muharram 1393 (February 12, 1973)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/144/D%C3%A9cret%20n%C2%B02-72-622.pdf',
                'summary' => '​Decree No. 2-72-622 of 8 Muharram 1393 (February  12, 1973) delegating powers to the Minister of Mining as regards safety stocks relating to energy products',
                'date' => '1973',
            ],
            '143/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l%E2%80%99industrie,%20des%20mines%20et%20de%20la%20marine%20marchande%20n%C2%B0%20393-76%20' => [
                'title' => 'Order of the Minister of Trade, Industry, Mining, and Merchant Navy No. 393-76 of 27 Safar 1397 (February 17, 1977)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/143/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l%E2%80%99industrie,%20des%20mines%20et%20de%20la%20marine%20marchande%20n%C2%B0%20393-76%20.pdf',
                'summary' => 'Order of the Minister of Trade, Industry, Mining, and Merchant Navy No. 393-76 of 27th Safar 1397 (February 17, 1977) on Safety Stocks of Petroleum Products.',
                'date' => '1977',
            ],
            '142/Arr%C3%AAt%C3%A9%20du%20ministre%20de%20l%E2%80%99%C3%A9nergie%20et%20des%20mines%20n%C2%B0%20484-81' => [
                'title' => 'Order of the Minister of Energy and Mining No. 484-81 of 20 Rajab 1401 (May 25, 1981)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/142/Arr%C3%AAt%C3%A9%20du%20ministre%20de%20l%E2%80%99%C3%A9nergie%20et%20des%20mines%20n%C2%B0%20484-81.pdf',
                'summary' => 'Order of the Minister of Energy and Mining No. 484-81 of 20 Rajab 1401 (25 May 1981) on the conditions of use of the special margin for the financing of safety stocks of liquid and gaseous fuels',
                'date' => '1981',
            ],
            '141/Arr%C3%AAt%C3%A9%20du%20ministre%20de%20l%E2%80%99%C3%A9nergie%20et%20des%20mines%20n%C2%B01546-07%20' => [
                'title' => 'Order of the Minister of Energy and Mining No. 1546-07 of 18 Rajab 1428 (August 03, 2007)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/141/Arr%C3%AAt%C3%A9%20du%20ministre%20de%20l%E2%80%99%C3%A9nergie%20et%20des%20mines%20n%C2%B01546-07%20.pdf',
                'summary' => 'Order of the Minister of Energy and Mining No. 1546-07 of 18 Rajab 1428 (August 03, 2007) on the characteristics of large petroleum products',
                'date' => '2007',
            ],
            '140/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l%E2%80%99industrie,%20des%20mines,%20de%20l%E2%80%99artisanat%20et%20de%20la%20marine%20marchande%20n%C2%B0%20053-62' => [
                'title' => 'Order of the Minister of Trade, Industry, Mining, Crafts and Merchant Navy No. 053-62 of January 02, 1962',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/140/Arr%C3%AAt%C3%A9%20du%20ministre%20du%20commerce,%20de%20l%E2%80%99industrie,%20des%20mines,%20de%20l%E2%80%99artisanat%20et%20de%20la%20marine%20marchande%20n%C2%B0%20053-62.pdf',
                'summary' => '​Order of the Minister of Trade, Industry, Mining, Crafts, and Merchant Navy No. 053-62 of January 02, 1962 concerning the characteristics of liquefied petroleum gases',
                'date' => '1962',
            ],
            '121/Decret_2-90-352_CNEN_BO_4205_fr%20(002)' => [
                'title' => ' Decree No. 2-90-352 of May 5, 1993, establishing the National Council of Nuclear Energy.',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/121/Decret_2-90-352_CNEN_BO_4205_fr%20(002).pdf',
                'summary' => 'The National Council for Nuclear Energy (CNEN) instituted by Decree No. 2-90-352 of May 05, 1993',
                'date' => '1993',
            ],
            '120/loi-142-12' => [
                'title' => 'Law No. 142-12 of August 22, 2014 on nuclear and radiological safety and security and the establishment of the Moroccan Agency for Nuclear and Radiological Safety and Security.',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/120/loi-142-12.pdf',
                'summary' => 'The provisions of Law 142-12, enacted in August 2014, apply to all activities involving sources of ionizing radiation.',
                'date' => '2014',
            ],
            '119/151-176' => [
                'title' => 'Law 12-02 on Civil Liability for Nuclear Damage',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/119/151-176.pdf',
                'summary' => 'The purpose of Law 12-02 relating to Civil Liability for Nuclear Damage, enacted in 2005, is to provide civil compensation for damage that may be caused by peaceful uses of nuclear energy, in accordance with the provisions of the Vienna on civil liability for nuclear damage.',
                'date' => '2005',
            ],
            '116/loi%2038-16%20BO.68.26%20AR' => [
                'title' => 'Law No. 38-16 amending and supplementing Article 2 of the “Dahir” (Royal decree) No. 1-63-226 of 14 Rabii I 1383 (August 5, 1963)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/116/loi%2038-16%20BO.68.26%20AR.pdf',
                'summary' => '​Law No. 38-16 amending and supplementing Article 2 of the “Dahir” (Royal decree) No. 1-63-226 of 14 Rabii I 1383 (August 5, 1963) establishing the National Electricity Office (O.N.E.)',
                'date' => '1963',
            ],
            // No PDF Link Available
            // "120/loi-142-12" => [
            //     'title' => 'Law No. 54-14 amending and supplementing article 2 of the “Dahir” (Royal decree) No. 1-63-226 of 14 Rabii I 1383 (August 05, 1963)',
            //     'start_url' => "https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/120/loi-142-12.pdf",
            //     'summary' => 'Law No. 54-14 amending and supplementing Article 2 of the “Dahir” (Royal decree) No. 1-63-226 of 14 Rabii I 1383 (5 August 1963) establishing the National Electricity Office (O.N.E.) and Article 5 of the Law No. 40-09 relating to the National Electricity and Drinking Water Office "ONEE".',
            //     'date' => '1963'
            // ],
            '114/2015%20BO_6411_Ar%20D%C3%A9cret%202.15.772%20Acc%C3%A9s%20MT' => [
                'title' => 'Decree No. 2-15-772 of 14 Muharram 1437 (October 28, 2015)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/114/2015%20BO_6411_Ar%20D%C3%A9cret%202.15.772%20Acc%C3%A9s%20MT.pdf',
                'summary' => 'Decree No. 2-15-772 of 14 Muharram 1437 (October 28, 2015) relating to access to the national medium voltage electricity grid',
                'date' => '2015',
            ],
            '113/2016%20BO_6472_Ar-%20Loi%2048-15%20Cr%C3%A9ation%20ANRE' => [
                'title' => '"Dahir” (Royal decree) n ° 1-16-60 of 17 Sha’ban 1437 (May 24, 2016)',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/113/2016%20BO_6472_Ar-%20Loi%2048-15%20Cr%C3%A9ation%20ANRE.pdf',
                'summary' => '​“Dahir” (Royal decree) No. 1-16-60 of 17 Sha’ban 1437 (May 24, 2016) enacting Law No. 48-15 relating to the regulation of the sector of the electricity and the creation of the national electricity regulation authority.',
                'date' => '2014',
            ],
            '110/%D9%82%D8%B1%D8%A7%D8%B1%20%D8%AA%D8%AD%D8%AF%D9%8A%D8%AF%20%D9%85%D9%88%D8%A7%D9%82%D8%B9%20%D8%A7%D9%84%D8%A5%D9%86%D8%AA%D8%A7%D8%AC%20%D9%84%D9%84%D8%B7%D8%A7%D9%82%D8%A9%20%D8%A7%D9%84%D8%B1%D9%8A%D8%AD%D9%8A%D8%A9' => [
                'title' => 'Order defining areas for hosting sites could harbour facilities to produce energy from wind power source',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/110/%D9%82%D8%B1%D8%A7%D8%B1%20%D8%AA%D8%AD%D8%AF%D9%8A%D8%AF%20%D9%85%D9%88%D8%A7%D9%82%D8%B9%20%D8%A7%D9%84%D8%A5%D9%86%D8%AA%D8%A7%D8%AC%20%D9%84%D9%84%D8%B7%D8%A7%D9%82%D8%A9%20%D8%A7%D9%84%D8%B1%D9%8A%D8%AD%D9%8A%D8%A9.pdf',
                'summary' => 'As part of the new energy strategy which establishes the development of renewable energy as key priority, a law on renewable energies was promulgated by Dahir No. 1-10-16 of 26 Safar 1431 (11 February 2010) in view to achieving the goals set by the government and to give the necessary visibility to private operators in the field of renewable energy. To establish and stop the areas of development of wind power, and pursuant to Article 7 of Law No. 13.09 on the abovementioned renewable energies, Order No. 2657-11 defining areas intended to accommodate sites that could house the production facilities of electricity from wind energy source was promulgated on 20 Shawal 1432 (19 September 2011) and published in the Official Bulletin No. 5984.',
                'date' => '2010',
            ],
            '109/%D9%82%D8%B1%D8%A7%D8%B1%20%D8%A8%D8%AA%D8%AD%D8%AF%D9%8A%D8%AF%20%D9%86%D9%85%D9%88%D8%AF%D8%AC%20%D8%AF%D9%81%D8%AA%D8%B1%20%D8%A7%D9%84%D8%AA%D8%AD%D9%85%D9%84%D8%A7%D8%AA' => [
                'title' => 'Order setting the model specifications to accompany the application for final authorization for the commissioning of a plant for producing electrical energy from renewable energy source',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/109/%D9%82%D8%B1%D8%A7%D8%B1%20%D8%A8%D8%AA%D8%AD%D8%AF%D9%8A%D8%AF%20%D9%86%D9%85%D9%88%D8%AF%D8%AC%20%D8%AF%D9%81%D8%AA%D8%B1%20%D8%A7%D9%84%D8%AA%D8%AD%D9%85%D9%84%D8%A7%D8%AA.pdf',
                'summary' => "As part of the energy strategy developed in accordance with guidelines Hautes SA Majesty King Mohammed VI, may God's Assists, and erecting the development of renewable energy in key priority to address security of supply challenges, preservation the environment and sustainable development, the 13-09 law on renewable energies was promulgated by Dahir No. 1-10-16 of 26 Safar 1431 (11 February 2010). Final authorization for the commissioning of a plant for producing electricity from renewable energy source, is issued by the administration, given, among other things a specifications to accompany the request final authorization under section 5 of Decree No. 2-10-578 of 7 Jumada I 1432 (11 April 2011); The model of that Specification charts to be established by the Administration under the provisions of Law No. 13-09 cited above. The order determining the model of the specifications was published in BO No. 6266 of June 19, 2014.",
                'date' => '2014',
            ],
            '108/%D9%85%D8%B1%D8%B3%D9%88%D9%85%20%D9%88%D9%84%D9%88%D8%AC%20%D8%A7%D9%84%D8%B4%D8%A8%D9%83%D8%A9%20%D8%B0%D8%A7%D8%AA%20%D8%A7%D9%84%D8%AC%D9%87%D8%AF%20%D8%A7%D9%84%D9%85%D8%AA%D9%88%D8%B3%D8%B7' => [
                'title' => 'ACCESS TO THE MEDIUM VOLTAGE ELECTRIC GRID',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/108/%D9%85%D8%B1%D8%B3%D9%88%D9%85%20%D9%88%D9%84%D9%88%D8%AC%20%D8%A7%D9%84%D8%B4%D8%A8%D9%83%D8%A9%20%D8%B0%D8%A7%D8%AA%20%D8%A7%D9%84%D8%AC%D9%87%D8%AF%20%D8%A7%D9%84%D9%85%D8%AA%D9%88%D8%B3%D8%B7.pdf',
                'summary' => 'Pursuant to Article 5 of the Law 13-09 on renewable energy as amended and supplemented by Law 58-15, the Decree No. 2-15-772 on access medium voltage to the national grid makes it possible:',
                'date' => '2014',
            ],
            '107/%D9%84%D9%82%D8%A7%D9%86%D9%88%D9%86%20%D8%B1%D9%82%D9%85%2009-13%20_Ar%20' => [
                'title' => 'Law No. 13-09 on renewable energy',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/107/%D9%84%D9%82%D8%A7%D9%86%D9%88%D9%86%20%D8%B1%D9%82%D9%85%2009-13%20_Ar%20.pdf',
                'summary' => 'This law is falls within the framework of the High Royal Orientations outlined by His Majesty The King, The God Assists, which give priority to the development of renewable energy and sustainable development. It falls within the major objectives of the energy strategy adopted by the Ministry of Energy, Mines, Water and Environment in consultation with stakeholders and operators during the first conference on energy that have held under the patronage of His Majesty King Mohammed VI, may God Assists the October 6, 2009 under the theme "master all our energy future."',
                'date' => '2010',
            ],
            '106/%D8%A7%D9%84%D9%82%D8%A7%D9%86%D9%88%D9%86%20%D8%B1%D9%82%D9%85%2015-58' => [
                'title' => 'LAW No 58-15 AMENDING AND SUPPLEMENTING LAW No 13-09 RELATING TO RENEWABLE ENERGY',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/106/%D8%A7%D9%84%D9%82%D8%A7%D9%86%D9%88%D9%86%20%D8%B1%D9%82%D9%85%2015-58.pdf',
                'summary' => 'As part of the implementation of the second phase of the National Energy Strategy and after the launch of the major reform projects, in particular the photovoltaic roadmap, the law No. 13-09 was supplemented and amended by Law No. 58-15, published in Official Bulletin No. 6436 of 4 February 2016.',
                'date' => '2016',
            ],
            '105/%D9%85%D8%B1%D8%B3%D9%88%D9%85%20%D8%A7%D9%84%D9%82%D8%A7%D9%86%D9%88%D9%86%20%D8%B1%D9%82%D9%85%2009-13' => [
                'title' => 'Decree No. 2-10-578 of 7 Jumada I 1432 (11 April 2011) adopted in application of Law No. 13-09 on renewable energy',
                'start_url' => 'https://www.mem.gov.ma/Lists/Lst_Textes_Reglementaires/Attachments/105/%D9%85%D8%B1%D8%B3%D9%88%D9%85%20%D8%A7%D9%84%D9%82%D8%A7%D9%86%D9%88%D9%86%20%D8%B1%D9%82%D9%85%2009-13.pdf',
                'summary' => "​As part of the new energy strategy developed in accordance with guidelines Hautes SA Majesty King Mohammed VI, may God's Assists, and which establishes the development of renewable energies and major priority as the optimal way to Morocco to meet the challenges supply security, preserve the environment and ensure sustainable development, a law on renewable energies was promulgated by Dahir No. 1-10-16 of 26 Safar 1431 (11 February 2010) with a view to achieving the goals set by the government and to give the necessary visibility to private operators in the field of renewable energy. To establish and arrest procedures related to the terms and conditions of the constitution and the filing of the application for authorization and pursuant to Articles 5, 8, 17, 18, 28 and 29 of Law No. 13.09 on renewable energy, decree No. 2-10-578 for its implementation was issued on 7 Jumada I (11 April 2011). This decree defines and establishes an exhaustive rules and procedures governing the application:",
                'date' => '2011',
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        foreach ($this->getDocs() as $id => $doc) {
            $id = urldecode($id);
            $title = $doc['title'];
            $startUrl = $doc['start_url'];
            $date = $doc['date'];
            $summary = $doc['summary'];
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $startUrl,
                'view_url' => $startUrl,
                'source_unique_id' => (string) $id,
                'language_code' => 'eng',
                'primary_location_id' => 173834,
            ]);

            $meta = [
                'summary' => trim($summary),
                'work_date' => trim($date),
            ];

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
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
        $catalogueDoc->load('docMeta');
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 173834);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $catalogueDoc->docMeta?->doc_meta['summary'] ?? null);
        $date = $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null;
        if ($date) {
            try {
                /** @var Carbon */
                $formattedDate = Carbon::parse($date);
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $formattedDate);
            } catch (InvalidFormatException $th) {
            }
        }

        $doc->docMeta->save();

        return $doc;
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getMoroccoStartUrls(): array
    {
        return ['type_' . CrawlType::FULL_CATALOGUE->value => [
            new UrlFrontierLink(['url' => 'https://www.mem.gov.ma/en/Pages/TextesReglementaires.aspx']),
        ]];
    }
}
