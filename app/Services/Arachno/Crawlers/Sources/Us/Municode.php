<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\TocItemDraft;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * slug: us-municode
 * title: Municode
 * url: https://library.municode.com.
 */
class Municode extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/municode\.com\/States/')) {
            $this->handleCatalogueStateIndex($pageCrawl);
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/municode\.com\/Clients\/stateAbbr/')) {
            $this->handleCatalogueClientIndex($pageCrawl);
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/municode\.com\/ClientContent\//')) {
            $this->handleCatalogueClientContent($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/municode\.com\/Jobs\/latest\//')) {
            $json = $pageCrawl->getJson();
            if (!isset($json['Id'])) {
                return;
            }
            $jobId = $json['Id'];
            $productId = $json['ProductId'];
            $url = 'https://api.municode.com/codesToc?jobId=' . $jobId . '&productId=' . $productId;
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url, 'anchor_text' => '']));
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/municode\.com\/codesToc\/children/')) {
            $this->handleMunicodeTocChildren($pageCrawl);
        } elseif ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/municode\.com\/codesToc\?/')) {
            $this->handleMunicodeToc($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/municode\.com\/CodesContent/')) {
            $this->handleMunicodeContent($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueStateIndex(PageCrawl $pageCrawl): void
    {
        $states = $pageCrawl->getJson();

        foreach ($states as $state) {
            $abbrev = strtolower($state['StateAbbreviation']);
            $url = 'https://api.municode.com/Clients/stateAbbr?stateAbbr=' . $abbrev;
            $l = new UrlFrontierLink(['url' => $url, 'anchor_text' => $state['StateName']]);
            $this->arachno->followLink($pageCrawl, $l);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueClientIndex(PageCrawl $pageCrawl): void
    {
        $clients = $pageCrawl->getJson();

        foreach ($clients as $client) {
            $id = $client['ClientID'];
            $url = 'https://api.municode.com/ClientContent/' . $id;
            $docMeta = new DocMetaDto();
            $docMeta->title = $client['ClientName'] . ', ' . $client['State']['StateName'];
            // we'll just use the work_number to store the state abbreviation for later
            $docMeta->work_number = $client['State']['StateAbbreviation'];
            $l = new UrlFrontierLink(['url' => $url, 'anchor_text' => $client['ClientName']]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueClientContent(PageCrawl $pageCrawl): void
    {
        $content = $pageCrawl->getJson();
        $meta = $pageCrawl->getFrontierMeta();
        $locations = $this->getMunicodeLocations();
        foreach ($content['codes'] as $code) {
            $title = $code['productName'] . ' - ' . $meta['title'];
            // e.g. "2022-12-07T20:04:43"
            $lastUpdated = $code['latestUpdatedDate'] ?? null;
            $lastUpdated = $lastUpdated ? str_replace('T', ' ', $lastUpdated) : null;
            $productId = $code['productId'];
            $location = $locations[$productId] ?? null;
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => 'https://api.municode.com/Jobs/latest/' . $code['productId'],
                'view_url' => 'https://library.municode.com/?productId=' . $code['productId'],
                'source_unique_id' => (string) $code['productId'],
                'language_code' => 'eng',
                'last_updated_at' => $lastUpdated,
                'primary_location_id' => $location,
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            // state abbreviation was stored in meta work_number
            $stateAbbrev = 'USA Cities and Counties - ' . $meta['work_number'];
            $this->arachno->addCategoriesToCatalogueDoc($catalogueDoc, [$stateAbbrev]);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleMunicodeToc(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $catDoc = $pageCrawl->pageUrl->catalogueDoc;

        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $json['Id']);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catDoc->title ?? '');
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catDoc->primary_location_id ?? '');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catDoc->view_url ?? null);
        $this->arachno->saveDoc($pageCrawl);
        $this->handleMunicodeTocChildren($pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleMunicodeTocChildren(PageCrawl $pageCrawl): void
    {
        $url = $pageCrawl->getFinalRedirectedUrl();
        $json = $pageCrawl->getJson();
        $jobId = Str::before(Str::after($url, 'jobId='), '&');
        $productId = Str::after($url, 'productId=');
        $children = $json['Children'] ?? $json;
        $items = [];
        $sourceUrl = 'https://library.municode.com/?productId=' . $productId . '&nodeId=';
        foreach ($children as $child) {
            $nodeId = $child['Id'];
            $followLinks = $child['HasChildren']
                ? [new UrlFrontierLink(['url' => 'https://api.municode.com/codesToc/children?jobId=' . $jobId . '&nodeId=' . $nodeId . '&productId=' . $productId, 'anchor_text' => $child['Heading']])]
                : [];

            $href = 'https://api.municode.com/CodesContent?jobId=' . $jobId . '&nodeId=' . $nodeId . '&productId=' . $productId;

            // if we've already fetched content for the parent, it means all content was fetched already
            $fetchedContentAtNode = Cache::pull('arachno.municode.meta.captured-content.' . $productId . '.' . $child['ParentId']);
            if ($fetchedContentAtNode) {
                Cache::put('arachno.municode.meta.captured-content.' . $productId . '.' . $child['Id'], $fetchedContentAtNode, 60 * 60 * 6);
                // we'll just provide the same link, which won't be added to the frontier then, but a TocITem will still be created
                $href = 'https://api.municode.com/CodesContent?jobId=' . $jobId . '&nodeId=' . $fetchedContentAtNode . '&productId=' . $productId;
            }

            $items[] = new TocItemDraft([
                // e.g. https://api.municode.com/CodesContent?jobId=392961&productId=16753
                'href' => $href . '#' . $nodeId,
                'links' => $followLinks,
                'source_url' => $sourceUrl . $nodeId,
                'label' => $child['Heading'],
                'source_unique_id' => '#' . $nodeId,
            ]);
        }

        $this->arachno->captureTocFromDraftArray($pageCrawl, $items);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleMunicodeContent(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $html = '';
        if ($json['ShowToc'] === true) {
            return;
        }
        $url = $pageCrawl->getFinalRedirectedUrl();
        $productId = Str::after($url, 'productId=');
        $nodeId = Str::before(Str::after($url, 'nodeId='), '&');
        Cache::put('arachno.municode.meta.captured-content.' . $productId . '.' . $nodeId, $nodeId, 60 * 60 * 6);

        foreach ($json['Docs'] as $doc) {
            $html .= '<a id="' . $doc['Id'] . '"></a>';
            $title = $doc['TitleHtml'];
            if (!is_null($title)) {
                $html .= $title;
            }
            $content = $doc['Content'];
            if (!is_null($content)) {
                $html .= $content;
            }
        }

        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::FUNC,
                'query' => fn () => $html,
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '[data-image-filename^="Municode"],[src~="Municode_Title_Page_Logo.png"]',
            ],
        ]);
        $this->arachno->capture($pageCrawl, $bodyQueries, [], $removeQueries);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string|int, int>
     */
    private function getMunicodeLocations(): array
    {
        return [
            '16509' => 173450, // https://library.municode.com/il/mundelein/codes/code_of_ordinances
            '16286' => 2591, // https://library.municode.com/ca/contra_costa_county/codes/ordinance_code
        ];
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-municode',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://api.municode.com/States/',
                    ]),
                ],
            ],
            'css' => '.chunks a{text-decoration:underline;color:#096fcc}#mcc_desktop_codes_mini_toc{display:none}.chunks{padding:0}.chunk-heading{display:table;width:100%;margin:8px 0;font-weight:700;line-height:1.3;text-rendering:optimizelegibility}.chunk-label,.chunk-title-wrapper{display:table-cell;vertical-align:middle}.mcc_codes_content_action_bar{display:table-cell;vertical-align:middle;width:36px;position:relative;right:-10px}.chunk-title{display:inline-block;font-weight:bold;}.chunk-nav{padding:12px 0}.footnote-content{line-height:20px;font-style:italic;margin:0 20px 13px 20px;border-left:1px solid #ccc;font-size:smaller;padding-left:20px;font-family:sans-serif}.footnote-content>p{margin:0!important}.chunk[data-nodedepth="0"] .chunk-title-wrapper{font-size:30px;line-height:40px}.chunk[data-nodedepth="1"] .chunk-title-wrapper{font-size:24px;line-height:34px}.chunk[data-nodedepth="2"] .chunk-title-wrapper{font-size:20px;line-height:30px}.chunk[data-nodedepth="3"] .chunk-title-wrapper{font-size:18px;line-height:28px}.chunk[data-nodedepth="4"] .chunk-title-wrapper{font-size:16px;line-height:26px}.chunk[data-nodedepth="5"] .chunk-title-wrapper{font-size:14px;line-height:24px}.chunk-content a img{margin-right:1em;margin-left:1em}p.bc{text-align:center}p.content0,p.content1,p.content2,p.content3,p.content4,p.content5,p.content6,p.content7,p.content8,p.content9,p.contentlistheader1,p.contentlistheader2,p.contentlistheader3,p.contentlistheader4,p.contentlistheader5,p.contentlistheader6,p.contentlistheader7{display:table-cell;border-bottom:4px solid transparent;border-left:.5rem solid transparent}.listheader0+p.contentlistheader1,.listheader1+p.contentlistheader2,.listheader2+p.contentlistheader3,.listheader3+p.contentlistheader4,.listheader4+p.contentlistheader5,.listheader5+p.contentlistheader6,.listheader6+p.contentlistheader7{display:block}p.contentlistheader1{margin-left:2rem}p.contentlistheader2{margin-left:4.5rem}p.contentlistheader3{margin-left:7rem}p.contentlistheader4{margin-left:9.5rem}p.contentlistheader5{margin-left:12rem}p.contentlistheader6{margin-left:14.5rem}p.contentlistheader7{margin-left:17rem}p.incr_ml0{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml1{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml2{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml3{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml4{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml5{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml6{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml7{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml8{float:left;margin:.2rem 0 0 0;width:2rem}p.incr_ml9{float:left;margin:.2rem 0 0 0;width:2rem}p.incr0,p.incr1,p.incr2,p.incr3,p.incr4,p.incr5,p.incr6,p.incr7,p.incr8{float:left;width:auto;margin-bottom:.2rem;position:static;width:2rem;overflow-wrap:break-word;text-align:right;margin-left:0;margin-right:.5rem}p.incr0{width:6rem}p.incr1{width:8.5rem}p.incr2{width:11rem}p.incr3{width:13.5rem}p.incr4{width:16rem}p.incr5{width:18.5rem}p.incr6{width:21rem}p.incr7{width:23.5rem}p.incr8{width:26rem}p.hg0,p.hg0-indent1,p.hg0-indent2,p.hg0-indent3,p.hg0-indent4,p.hg1,p.hg1-indent1,p.hg1-indent2,p.hg1-indent3,p.hg1-indent4,p.hg2,p.hg2-indent1,p.hg2-indent2,p.hg2-indent3,p.hg2-indent4,p.hg3,p.hg3-indent1,p.hg3-indent2,p.hg3-indent3,p.hg3-indent4,p.hg4,p.hg4-indent1,p.hg4-indent2,p.hg4-indent3,p.hg4-indent4,p.hg5,p.hg5-indent1,p.hg5-indent2,p.hg5-indent3,p.hg5-indent4,p.hg6,p.hg6-indent1,p.hg6-indent2,p.hg6-indent3,p.hg6-indent4,p.hg7,p.hg7-indent1,p.hg7-indent2,p.hg7-indent3,p.hg7-indent4,p.hg8,p.hg8-indent1,p.hg8-indent2,p.hg8-indent3,p.hg8-indent4,p.hg9,p.hg9-indent1,p.hg9-indent2,p.hg9-indent3,p.hg9-indent4{text-indent:-2.5rem;margin-top:.2rem;margin-bottom:1rem}p.b0{margin-left:6rem}.b1Class{margin-left:7rem}p.b1,p.hg0,p.hg0-indent1,p.hg0-indent2,p.hg0-indent3,p.hg0-indent4{margin-left:7rem}p.b2,p.hg1,p.hg1-indent1,p.hg1-indent2,p.hg1-indent3,p.hg1-indent4{margin-left:9.5rem}p.b3,p.hg2,p.hg2-indent1,p.hg2-indent2,p.hg2-indent3,p.hg2-indent4{margin-left:12rem}p.b4,p.hg3,p.hg3-indent1,p.hg3-indent2,p.hg3-indent3,p.hg3-indent4{margin-left:14.5rem}p.b5,p.hg4,p.hg4-indent1,p.hg4-indent2,p.hg4-indent3,p.hg4-indent4{margin-left:17rem}p.b6,p.hg5,p.hg5-indent1,p.hg5-indent2,p.hg5-indent3,p.hg5-indent4{margin-left:19.5rem}p.b7,p.hg6,p.hg6-indent1,p.hg6-indent2,p.hg6-indent3,p.hg6-indent4{margin-left:22rem}p.b8,p.hg7,p.hg7-indent1,p.hg7-indent2,p.hg7-indent3,p.hg7-indent4{margin-left:24.5rem}p.hg8,p.hg8-indent1,p.hg8-indent2,p.hg8-indent3,p.hg8-indent4{margin-left:27rem}p.hg9,p.hg9-indent1,p.hg9-indent2,p.hg9-indent3,p.hg9-indent4{margin-left:29.5rem}p.historynote{color:#777;font-weight:400;font-size:.857em;font-style:italic;line-height:1.4em}.ulsingle{text-decoration:underline}.ulstrike{text-decoration:line-through}.uloverrule{text-decoration:overline}.ulxpp04{text-decoration:underline}.ulxpp05{text-decoration:underline}.ulxpp06{text-decoration:underline}.ulxpp07{text-decoration:underline}.ulxpp08{text-decoration:underline}.ulxpp09{text-decoration:underline}.ulxpp10{text-decoration:underline}.tableC{margin:.2rem 0 0 150px;color:#000;font-weight:400;font-size:10pt}.t{border-top-color:#000;border-top-style:solid;border-top-width:1px}.r{border-right-color:#000;border-right-style:solid;border-right-width:1px}.b{border-bottom-color:#000;border-bottom-style:solid;border-bottom-width:1px}.l{border-left-color:#000;border-left-style:solid;border-left-width:1px}.sig_table{margin:.2rem 0 0 3rem;text-indent:0;border-style:none;width:90%}.sig_table_cell{text-indent:0;border-style:none}p.pc0{margin:.2rem 1rem 1rem 0;background:#eee;padding:10px}p.pc1{margin:.2rem 0 1rem 0}p.pc2{margin:.2rem 0 1rem 0}p.pc3{margin:.2rem 0 1rem 0}p.pc4{margin:.2rem 0 1rem 0}p.pc5{margin:.2rem 0 1rem 0}p.pc6{margin:.2rem 0 1rem 0}p.pc7{margin:.2rem 0 1rem 0}p.pc8{margin:.2rem 0 1rem 0}p.pc9{margin:.2rem 0 1rem 0}p.p0{margin:.2rem 0 1rem 0}p.p1{margin:.2rem 0 1rem 2rem}p.p2{margin:.2rem 0 1rem 4rem}p.p3{margin:.2rem 0 1rem 6rem}p.p4{margin:.2rem 0 1rem 8rem}p.p5{margin:.2rem 0 1rem 10rem}p.p6{margin:.2rem 0 1rem 12rem}p.p7{margin:.2rem 0 1rem 14rem}p.p8{margin:.2rem 0 1rem 16rem}p.p9{margin:.2rem 0 1rem 18rem}p.p0,p.p1,p.p2,p.p3,p.p4,p.p5,p.p6,p.p7,p.p8,p.p9{text-indent:2rem}.hanging-indent{text-indent:-25px;padding-left:25px}',
        ];
    }
}
