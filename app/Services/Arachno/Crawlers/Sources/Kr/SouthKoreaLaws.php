<?php

namespace App\Services\Arachno\Crawlers\Sources\Kr;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\TocItemDraft;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: kr-south-korea-laws
 * title: South Korea Laws
 * url: https://www.law.go.kr/
 *
 * There are three sections of the South Korea law website that we're ingesting. Google translate translates them as
 * "Statutes", "self governing laws" and "administrative rules". The "self governing laws" are the provinces and
 * municipalities. Each section has a slightly different way of indexing and showing the documents, hence having to
 * check for the section at various stages. There are quite a number of POST requests with query parameters for
 * filtering and displaying the page. So we need to add those parameters in the preFetch method.
 */
class SouthKoreaLaws extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'kr-south-korea-laws',
            'throttle_requests' => 400,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.law.go.kr/lsSc.do?menuId=1&subMenuId=15&tabMenuId=81&query=',
                    ]),
                ],
            ],
            'css' => 'ul.lawico01 {  float:left; width:44px; padding:0; margin-top: 4px; }ul.lawico01 li { float:left; padding:2px;}ul.lawico01 li img { vertical-align : top;}div.lawcon { overflow:hidden;}div.pgroup{ margin:0 10px 22px 0; line-height:190%; clear:both; font-size:1.1em; }div.pgroup p.pty1_p1{ padding: 0 0 0 32px;    text-indent: -30px; margin:0 0 0 0;}div.pgroup p.pty1_p2{ padding: 0 0 0 35px; text-indent: -18px; margin:0 0 0 0;}div.pgroup p.pty1_p4{ padding: 0 0 0 32px;    text-indent: -30px; margin:0 0 0 0;}div.pgroup p.pty1_p5{ padding: 0 0 0 20px; text-indent: -15px; margin:0 0 0 0;}div.pgroup p.pty1_p6 {line-height: 20px;padding: 0 0 0 20px;text-indent: -15px;margin: 0 0 0 0;}div.pgroup p.pty1_p11{  padding:7px 0 0 65px;  padding-left:40px;  text-indent:-36px;  margin:0 0 0 0;}div.pgroup p.pty1_p12{  padding:7px 0 0 65px;  padding-top:0px;  text-indent:-36px;  margin:0 0 0 0;}div.pgroup p.pty1_de2{  padding:0 0 0 37px;  margin:0;}div.pgroup p.pty1_de2_1{  padding:0 0 0 33px;  text-indent:-2px;  margin:0;}div.pgroup p.pty1_de2h{  padding:0 0 0 48px;  text-indent:-15px;  margin:0;}div.pgroup p.pty1_de3{  padding:0 0 0 65px;  text-indent:-17px;  margin:0;}/* 1. 媛� 1) */div.pgroup p.pty1_de4_1{  padding:0 0 0 64px;  text-indent:-2px;  margin:0;}div.pgroup p.pty1{  padding:7px 0 0 35px;  text-indent:-36px;  margin:0 0 0 0;}div.pgroup p.pty1_ye1 { padding: 7px 0 0 32px;    text-indent: -15px; margin:0 0 0 0;}div.pgroup p.pty3{ padding: 10px 0 0 86px;    text-indent: -49px; margin:0 0 10px 0;}div.pgroup p.pty3_dep1{ padding:0 0 0 82px; text-indent:-17px; margin:0 0 0 0;}div.pgroup p.pty3_dep2{ padding:0 0 0 82px; text-indent:0px; margin:0 0 0 0;}div.pgroup p.pty3_dep3{ padding:0 0 0 75px; text-indent:-19px; margin:0 0 0 0;}div.pgroup p.pty3_dep4{ padding:0 0 0 85px; text-indent:-19px; margin:0 0 0 0;}div.pgroup p span.bl{ color:#151594; font-weight:bold;}div.babl{ background:#d5e6ed; padding-left:50px;}ul.cont_icon { width:350px; margin:4px 0 0 2px; }ul.cont_icon li { float:left; padding-right:7px;}ul.cont_icon li img { vertical-align:top; padding-right:4px; margin-top:-1px}ul.cont_icon li a { vertical-align:middle; text-decoration:none;} .cont_top{padding:20px 0 5px 0}.cont_top h2{padding:0;font-size:19px;text-align:center}.cont_top span{color:#444;font-size:12px;font-weight:normal;letter-spacing:0}.ct_sub{margin-top:10px;text-align:center}.ct_sub + .ct_sub{margin-top:2px}.ct_sub span{display:inline-block;margin-top:3px;font-size:12px;color:#3c7fbc;vertical-align:top}.cont_subtit{margin-bottom:10px;padding:0 20px}.cont_subtit p + p{margin-top:4px}.cont_subtit p{margin:0;color:#444;font-size:12px;text-align:right}.scr_ctrl{overflow:hidden}.scr_ctrl .scr_area {overflow-y:auto; -webkit-overflow-scrolling:touch;}li.csmLnkImg {display:none;}',
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/lsSc\.do\?menuId/')) {
            // matches the start url
            $this->startFullCatalogueCrawl($pageCrawl);
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && ($this->arachno->matchUrl($pageCrawl, '/lsScListR\.do/') || $this->arachno->matchUrl($pageCrawl, '/admRulScListR\.do/') || $this->arachno->matchUrl($pageCrawl, '/ordinScListR\.do/'))
        ) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && ($this->arachno->matchUrl($pageCrawl, '/lsInfoR\.do/')
                || $this->arachno->matchUrl($pageCrawl, '/ordinInfoR\.do/')
                || $this->arachno->matchUrl($pageCrawl, '/admRulLsInfoR\.do/'))
        ) {
            // HTML full text content
            $this->handleFullText($pageCrawl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        // index pages statutes
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/lsScListR\.do\?menuId/')) {
            $url = $pageCrawl->pageUrl->url;
            $page = (int) (string) Str::of($url)->afterLast('&pg=');

            $settings = [
                'method' => 'POST',
                'options' => [
                    'form_params' => [
                        'q' => '*',
                        'outmax' => 500,
                        'p2' => 3,
                        'p18' => 0,
                        'p19' => '1,3',
                        'pg' => $page,
                        'fsort' => '20,11,31',
                        'lsType' => null,
                        'section' => 'lawNm',
                        'lsiSeq' => 0,
                        'p9' => '2,4',
                    ],
                ],
            ];

            $pageCrawl->setHttpSettings($settings);
        }
        // index pages administrative rules
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/admRulScListR\.do\?menuId/')) {
            $url = $pageCrawl->pageUrl->url;
            $page = (int) (string) Str::of($url)->afterLast('&pg=');

            $settings = [
                'method' => 'POST',
                'options' => [
                    'form_params' => [
                        'q' => '*',
                        'outmax' => 500,
                        'pg' => $page,
                        'p6' => '2,3',
                        'fsort' => '61,20,11,31',
                        'section' => 'admRulNm',
                        'admRulSeq' => '0',
                        'dtlYn' => 'N',
                        'admType' => 'N',
                        'p10' => '911801,911802,911804,0',
                        'p15' => '1,3',
                    ],
                ],
            ];

            $pageCrawl->setHttpSettings($settings);
        }
        // index pages municipal
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/ordinScListR\.do\?menuId/')) {
            $url = $pageCrawl->pageUrl->url;
            $page = (int) (string) Str::of($url)->afterLast('&pg=');
            // province
            $p7 = (string) Str::of($url)->afterLast('&p7=')->before('&');
            // city
            $p1 = (string) Str::of($url)->afterLast('&p1=')->before('&');

            $settings = [
                'method' => 'POST',
                'options' => [
                    'form_params' => [
                        'q' => '*',
                        'outmax' => 500,
                        'pg' => $page,
                        'p3' => '3',
                        'p1' => $p1,
                        'p7' => $p7,
                        'fsort' => '21,11,31',
                        'idxList' => 'LsKwdNm_idx,OrdinNm_idx',
                        'section' => 'ordinNm',
                        'dtlYn' => 'N',
                    ],
                ],
            ];

            $pageCrawl->setHttpSettings($settings);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/lsInfoR\.do/')) {
            // fetch full text for statutes
            $url = $pageCrawl->pageUrl->url;
            $id = (string) Str::of($url)->afterLast('?id=');
            $date = $pageCrawl->pageUrl->catalogueDoc?->post_data['date'] ?? null;

            $settings = [
                'method' => 'POST',
                'options' => [
                    'form_params' => [
                        'lsiSeq' => $id,
                        'efYd' => $date,
                        'ancYnChk' => 0,
                    ],
                ],
            ];

            $pageCrawl->setHttpSettings($settings);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/admRulLsInfoR\.do/')) {
            // fetch full text for administrative rules
            $url = $pageCrawl->pageUrl->url;
            $id = (string) Str::of($url)->afterLast('?id=');

            $settings = [
                'method' => 'POST',
                'options' => [
                    'form_params' => [
                        'admRulSeq' => $id,
                    ],
                ],
            ];

            $pageCrawl->setHttpSettings($settings);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function startFullCatalogueCrawl(PageCrawl $pageCrawl): void
    {
        // the pagination is actually passed via form params, but to make each page unique, we'll add it in the URL as query parameter - pg=1
        $this->arachno->followLink(
            $pageCrawl,
            new UrlFrontierLink(['url' => 'https://www.law.go.kr/lsScListR.do?menuId=1&subMenuId=15&tabMenuId=81&pg=1'])
        );
        // administrative rules
        $this->arachno->followLink(
            $pageCrawl,
            new UrlFrontierLink(['url' => 'https://www.law.go.kr/admRulScListR.do?menuId=5&subMenuId=41&tabMenuId=183&pg=1'])
        );
        // municipal
        $provinces = $this->getMunicipalities();
        foreach ($provinces as $p7 => $province) {
            $this->arachno->followLink(
                $pageCrawl,
                new UrlFrontierLink(['url' => 'https://www.law.go.kr/ordinScListR.do?menuId=3&subMenuId=27&tabMenuId=139&p7=' . $p7 . '&p1=' . $p7 . '&pg=1'])
            );

            foreach (array_keys($province['municipalities']) as $muniId) {
                $this->arachno->followLink(
                    $pageCrawl,
                    new UrlFrontierLink(['url' => 'https://www.law.go.kr/ordinScListR.do?menuId=3&subMenuId=27&tabMenuId=139&p7=' . $p7 . '&p1=' . $muniId . '&pg=1'])
                );
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $section = '';
        $url = $pageCrawl->pageUrl->url;
        if (str_contains($url, 'admRulScListR.do')) {
            $section = 'administrative rules';
            $viewUrlBase = 'https://www.law.go.kr/admRulLsInfoP.do?admRulSeq=';
        } elseif (str_contains($url, 'ordinScListR.do')) {
            $section = 'municipal';
            $viewUrlBase = 'https://www.law.go.kr/ordinInfoP.do?ordinSeq=';
        } else {
            $section = 'statutes';
            $viewUrlBase = 'https://www.law.go.kr/lsInfoP.do?lsiSeq=';
        }
        $this->arachno->each($pageCrawl, '#viewHeightDiv tr', function (DOMElement $tr) use ($pageCrawl, $section, $viewUrlBase) {
            /** @var DOMElement $tr */
            $trCrawler = new DomCrawler($tr);
            /** @var DOMElement $link */
            $link = $trCrawler->filter('td')->getNode(0);
            $meta = [];
            $url = '';
            $title = '';
            $id = $date = null;
            foreach ($trCrawler->filter('td') as $index => $td) {
                /** @var DOMElement $td */
                if ($section === 'statutes') {
                    if ($index === 1) {
                        [$id, $title, $date, $url] = $this->getTitleUrlId($td, $section);
                        if (!$id) {
                            return;
                        }
                    }
                    if ($index === 2) {
                        $meta['work_date'] = $this->extractDate($td);
                    }
                    if ($index === 3) {
                        $meta['original_work_type'] = $td->textContent ?? null;
                        $meta['work_type'] = $this->extractWorkType($td);
                    }
                    if ($index === 4) {
                        $meta['work_number'] = $td->textContent ?? null;
                    }
                    if ($index === 5) {
                        $meta['effective_date'] = $this->extractDate($td);
                    }
                    if ($index === 7) {
                        // just keeping this as interesting info... possibly use for categories
                        $meta['department'] = $td->textContent ?? null;
                    }
                } elseif ($section === 'administrative rules') {
                    if ($index === 1) {
                        [$id, $title, $date, $url] = $this->getTitleUrlId($td, $section);
                        if (!$id) {
                            return;
                        }
                    }
                    if ($index === 2) {
                        $meta['original_work_type'] = $td->textContent ?? null;
                        $meta['work_type'] = $this->extractWorkType($td);
                    }
                    if ($index === 3) {
                        $meta['work_number'] = $td->textContent ?? null;
                    }
                    if ($index === 4) {
                        $meta['work_date'] = $this->extractDate($td);
                    }
                    if ($index === 5) {
                        $meta['effective_date'] = $this->extractDate($td);
                    }
                    if ($index === 8) {
                        // just keeping this as interesting info... possibly use for categories
                        $meta['department'] = $td->textContent ?? null;
                    }
                } elseif ($section === 'municipal') {
                    if ($index === 1) {
                        $meta['municipality'] = $td->textContent ?? null;
                    }
                    if ($index === 2) {
                        [$id, $title, $date, $url] = $this->getTitleUrlId($td, $section);
                        if (!$id) {
                            return;
                        }
                    }
                    if ($index === 3) {
                        $meta['work_type'] = $this->extractWorkType($td);
                    }
                    if ($index === 4) {
                        $meta['work_number'] = $td->textContent ?? null;
                    }
                    if ($index === 5) {
                        $meta['work_date'] = $this->extractDate($td);
                    }
                }
            }
            if (!$id || !$title) {
                return;
            }

            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $url,
                'view_url' => $viewUrlBase . $id,
                'source_unique_id' => (string) $id,
                'language_code' => 'kor',
                'post_data' => $date ? ['date' => $date] : null,
                'primary_location_id' => $section === 'municipal' ? $this->getMunicipalityFromIndexUrl($pageCrawl->pageUrl->url) : '155958',
            ]);
            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $cats = match ($section) {
                'statutes' => ['South Korea Laws - Statutes'],
                'administrative rules' => ['South Korea Laws - Administrative Rules'],
                'municipal' => ['South Korea Laws - Provinces and Municipalities'],
            };
            $this->arachno->addCategoriesToCatalogueDoc($catalogueDoc, $cats);
        });

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/&pg=/')) {
            $tags = $pageCrawl->domCrawler->filter('div.paging a');
            if ($tags->count() > 0) {
                /** @var DOMElement|null $lastPageElem */
                $lastPageElem = $tags->getNode($tags->count() - 1);
                $lastPage = $lastPageElem?->getAttribute('onclick') ?? '';
                $lastPage = (int) Str::of($lastPage)->afterLast("('")->before("')")->toString();

                $page = (int) (string) Str::of($pageCrawl->pageUrl->url)->afterLast('&pg=');

                if ($lastPage && $page < $lastPage) {
                    $page++;
                    $nextPageUrl = (string) Str::of($pageCrawl->pageUrl->url)->before('&pg=');
                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "{$nextPageUrl}&pg={$page}"]));
                }
            }
        }

        //        if ($pageCrawl->domCrawler->filter('a > img[src*="page_next.gif"]')->count() > 0) {
        //            $page = (int) (string) Str::of($pageCrawl->pageUrl->url)->afterLast('&pg=');
        //            $nextPage = $page + 1;
        //            $nextPageUrl = (string) Str::of($pageCrawl->pageUrl->url)->before('&pg=');
        //            // go to next page of search results
        //            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $nextPageUrl . '&pg=' . $nextPage]));
        //        }
    }

    private function extractDate(DOMElement $td): ?string
    {
        $dateTxt = $td->textContent;
        if ($dateTxt) {
            $date = null;
            try {
                $date = Carbon::createFromFormat('Y. m. j.', $dateTxt);
            } catch (InvalidFormatException $th) {
            }
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function extractWorkType(DOMElement $td): string
    {
        $workType = trim($td->textContent);

        return match ($workType) {
            '대통령령', '법무부령' => 'decree',
            '대통령긴급명령' => 'order',
            '경제기획원령' => 'ordinance',
            '조례' => 'bylaw',
            '중앙선거관리위원회규칙', '국회규칙', '대법원규칙', '규칙' => 'rule',
            default => 'act',
        };
    }

    /**
     * @param DOMElement $td
     * @param string     $section
     *
     * @return array<mixed>
     */
    private function getTitleUrlId(DOMElement $td, string $section): array
    {
        $baseUrl = match ($section) {
            'statutes' => 'https://www.law.go.kr/lsInfoR.do',
            'municipal' => 'https://www.law.go.kr/ordinInfoR.do',
            'administrative rules' => 'https://www.law.go.kr/admRulLsInfoR.do',
            default => 'https://www.law.go.kr/lsInfoR.do',
        };
        $tdCrawler = new DomCrawler($td);

        /** @var DOMElement|null */
        $aNode = $tdCrawler->filter('a')->getNode(0);
        if (is_null($aNode)) {
            return [null, null, null, null];
        }
        $title = $aNode->textContent ?? null;
        $onClick = $aNode->getAttribute('onclick');
        // we want to get the first two parameters from the lsViewWideAll lsViewWideAll('249727','20230605','liBgcolor1',$(this),'3','0','Y','81')
        $id = (string) Str::of($onClick)->after("('")->before("'");

        $date = $section === 'statutes' ? (string) Str::of($onClick)->after("','")->before("','") : null;
        // the ID is actually passed via form data, but need to make the URL unique, so adding the id as query param
        $url = match ($section) {
            'statutes' => $baseUrl . '?id=' . $id,
            'municipal' => $baseUrl . '?ordinSeq=' . $id,
            'administrative rules' => $baseUrl . '?id=' . $id,
            default => $baseUrl . '?id=' . $id,
        };

        return [$id, $title, $date, $url];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleFullText(PageCrawl $pageCrawl): void
    {
        $id = (string) Str::of($pageCrawl->pageUrl->url)->afterLast('=');
        /** @var CatalogueDoc */
        $catDoc = $pageCrawl->pageUrl->catalogueDoc;
        /** @var CatalogueDocMeta */
        $catDocMeta = $catDoc->docMeta;
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'kor');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catDoc->primary_location_id ?? '155958');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catDocMeta->doc_meta['work_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catDocMeta->doc_meta['work_number'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catDocMeta->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $catDocMeta->doc_meta['work_type'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catDoc->view_url ?? null);

        $bodyQueries = [new DomQuery([
            'type' => DomQueryType::CSS,
            'query' => '#contentBody, .contentBody',
        ])];
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'input',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.pconfile a.blu img',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.byl_pop',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.pconfile a[href="javascript:;"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.lawico01',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.cont_icon li a',
            ],
        ]);

        $preStore = function (DomCrawler $domCrawler) {
            foreach ($domCrawler->filter('*[onclick]') as $el) {
                /** @var DOMElement $el */
                $el->removeAttribute('onclick');
            }
            // need to replace all anchor tag name and ID's becuase they use a colon, which will cause problems
            foreach ($domCrawler->filter('a[name]') as $el) {
                /** @var DOMElement $el */
                $tocId = str_replace(':', '_', $el->getAttribute('name'));
                $el->setAttribute('name', $tocId);
                $el->setAttribute('id', $tocId);
            }

            /** @var DOMElement $hrefElem */
            foreach ($domCrawler->filter('a[href*="AJAX"], a[href="javascript:;"]') as $hrefElem) {
                $hrefElem->removeAttribute('href');
            }

            return $domCrawler;
        };

        $this->handleKoreaToC($pageCrawl, $id);
        $this->arachno->capture($pageCrawl, $bodyQueries, [], $removeQueries, null, $preStore);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $id
     *
     * @return void
     */
    protected function handleKoreaToC(PageCrawl $pageCrawl, string $id): void
    {
        $section = '';
        $pageBase = '';
        $url = $pageCrawl->pageUrl->url;
        $expressionDate = $pageCrawl->pageUrl->catalogueDoc?->post_data['date'] ?? '';
        if (str_contains($url, 'admRulLsInfoR.do')) {
            $section = 'administrative rules';
            $pageBase = 'https://www.law.go.kr/admRulLsInfoP.do?admRulSeq=';
            $tocBase = 'https://www.law.go.kr/admRulJoListRInc.do?admRulSeq=';
        } elseif (str_contains($url, 'ordinInfoR.do')) {
            $section = 'municipal';
            $pageBase = 'https://www.law.go.kr/ordinInfoP.do?ordinSeq=';
            $tocBase = 'https://www.law.go.kr/ordinJoListRInc.do?ordinSeq=';
        } else {
            $section = 'statutes';
            $pageBase = 'https://www.law.go.kr/lsInfoP.do?lsiSeq=';
            $tocBase = 'https://www.law.go.kr/joListRInc.do?efYd=' . $expressionDate . '&lsiSeq=';
        }

        $response = Http::get($pageBase . $id);
        $dom = new DomCrawler($response->body());
        $tocItems = [];
        $otherItemsAdded = false;
        // there are usually two or three top level toc items that when clicked show the ToC below. We'll "click" those and get back JSON lists
        foreach ($dom->filter('#leftContent ul li.dep div.dep1 a') as $tocTopLink) {
            /** @var DOMElement $tocTopLink */
            $tocItems[] = new TocItemDraft([
                'label' => $tocTopLink->textContent ?? '',
                'source_unique_id' => $tocTopLink->textContent ?? '',
                'level' => 1,
            ]);
            $onclick = $tocTopLink->getAttribute('onclick');
            // no idea what the mode is for, but it seems important...
            $mode = (string) Str::of($onclick)->after("','")->before("'");
            $mode = $mode === '999' ? '99' : $mode;

            $rsp = Http::post($tocBase . $id . '&mode=' . $mode . '&ancYnChk=0');
            $tocData = $rsp->json();

            foreach ($tocData as $item) {
                $otherItemsAdded = true;
                $uniqueId = 'J' . str_replace(':', '_', $item['joLink']);
                $tocItems[] = new TocItemDraft([
                    'href' => $url . '#' . $uniqueId,
                    'label' => $item['joTit'],
                    'source_unique_id' => $uniqueId,
                    'level' => 2,
                ]);
            }
        }
        // if there are only top level toc items, then don't create any, as it's just linking to the main content
        if ($otherItemsAdded) {
            $this->arachno->captureTocFromDraftArray($pageCrawl, $tocItems);
        }
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getMunicipalityFromIndexUrl(string $url): string
    {
        // province
        $p7 = (string) Str::of($url)->afterLast('&p7=')->before('&');
        // city
        $p1 = (string) Str::of($url)->afterLast('&p1=')->before('&');

        // we're fetching province laws
        if ($p7 === $p1) {
            return (string) $this->getMunicipalities()[$p7]['location_id'];
        }

        return (string) $this->getMunicipalities()[$p7]['municipalities'][$p1]['location_id'];
    }

    /**
     * @return array<mixed>
     */
    public function getMunicipalities(): array
    {
        return [
            '6110000' => ['province' => 'Seoul', 'location_id' => 156168, 'municipalities' => ['3220000' => ['name' => '강남구', 'location_id' => 156312], '3240000' => ['name' => '강동구', 'location_id' => 156227], '3080000' => ['name' => '강북구', 'location_id' => 156237], '3150000' => ['name' => '강서구', 'location_id' => 156302], '3200000' => ['name' => '관악구', 'location_id' => 156273], '3040000' => ['name' => '광진구', 'location_id' => 156363], '3160000' => ['name' => '구로구', 'location_id' => 156294], '3170000' => ['name' => '금천구', 'location_id' => 156404], '3100000' => ['name' => '노원구', 'location_id' => 156380], '3090000' => ['name' => '도봉구', 'location_id' => 156222], '3050000' => ['name' => '동대문구', 'location_id' => 156212], '3190000' => ['name' => '동작구', 'location_id' => 156205], '3130000' => ['name' => '마포구', 'location_id' => 156257], '3120000' => ['name' => '서대문구', 'location_id' => 156247], '3210000' => ['name' => '서초구', 'location_id' => 156408], '3030000' => ['name' => '성동구', 'location_id' => 156351], '3070000' => ['name' => '성북구', 'location_id' => 156387], '3230000' => ['name' => '송파구', 'location_id' => 156418], '3140000' => ['name' => '양천구', 'location_id' => 156400], '3180000' => ['name' => '영등포구', 'location_id' => 156183], '3020000' => ['name' => '용산구', 'location_id' => 156169], '3110000' => ['name' => '은평구', 'location_id' => 156193], '3000000' => ['name' => '종로구', 'location_id' => 156337], '3010000' => ['name' => '중구', 'location_id' => 156326], '3060000' => ['name' => '중랑구', 'location_id' => 156371]]],
            '6260000' => ['province' => 'busan', 'location_id' => 156434, 'municipalities' => ['3360000' => ['name' => '강서구', 'location_id' => 156502], '3350000' => ['name' => '금정구', 'location_id' => 156509], '3400000' => ['name' => '기장군', 'location_id' => 156519], '3310000' => ['name' => '남구', 'location_id' => 156549], '3270000' => ['name' => '동구', 'location_id' => 156450], '3300000' => ['name' => '동래구', 'location_id' => 156442], '3290000' => ['name' => '부산진구', 'location_id' => 156531], '3320000' => ['name' => '북구', 'location_id' => 156543], '3390000' => ['name' => '사상구', 'location_id' => 156482], '3340000' => ['name' => '사하구', 'location_id' => 156473], '3260000' => ['name' => '서구', 'location_id' => 156455], '3380000' => ['name' => '수영구', 'location_id' => 156525], '3370000' => ['name' => '연제구', 'location_id' => 156491], '3280000' => ['name' => '영도구', 'location_id' => 156435], '3250000' => ['name' => '중구', 'location_id' => 156464], '3330000' => ['name' => '해운대구', 'location_id' => 156494]]],
            '6270000' => ['province' => 'daegu', 'location_id' => 156077, 'municipalities' => ['3440000' => ['name' => '남구', 'location_id' => 156116], '3470000' => ['name' => '달서구', 'location_id' => 156088], '3480000' => ['name' => '달성군', 'location_id' => 156078], '3420000' => ['name' => '동구', 'location_id' => 156143], '3450000' => ['name' => '북구', 'location_id' => 156126], '3430000' => ['name' => '서구', 'location_id' => 156107], '3460000' => ['name' => '수성구', 'location_id' => 156155], '3410000' => ['name' => '중구', 'location_id' => 156120]]],
            '6280000' => ['province' => 'Incheon', 'location_id' => 157437, 'municipalities' => ['3570000' => ['name' => '강화군', 'location_id' => 157438], '3550000' => ['name' => '계양구', 'location_id' => 157462], '3530000' => ['name' => '남동구', 'location_id' => 157477], '3500000' => ['name' => '동구', 'location_id' => 157492], '3510500' => ['name' => '미추홀구', 'location_id' => 157517], '3540000' => ['name' => '부평구', 'location_id' => 157468], '3560000' => ['name' => '서구', 'location_id' => 157452], '3520000' => ['name' => '연수구', 'location_id' => 157486], '3580000' => ['name' => '옹진군', 'location_id' => 157498], '3490000' => ['name' => '중구', 'location_id' => 157506]]],
            '6290000' => ['province' => 'gwangju', 'location_id' => 157167, 'municipalities' => ['3630000' => ['name' => '광산구', 'location_id' => 157168], '3610000' => ['name' => '남구', 'location_id' => 157200], '3590000' => ['name' => '동구', 'location_id' => 157229], '3620000' => ['name' => '북구', 'location_id' => 157210], '3600000' => ['name' => '서구', 'location_id' => 157188]]],
            '6300000' => ['province' => 'Daejeon', 'location_id' => 156009, 'municipalities' => ['3680000' => ['name' => '대덕구', 'location_id' => 156010], '3640000' => ['name' => '동구', 'location_id' => 156062], '3660000' => ['name' => '서구', 'location_id' => 156030], '3670000' => ['name' => '유성구', 'location_id' => 156022], '3650000' => ['name' => '중구', 'location_id' => 156047]]],
            '5690000' => ['province' => 'Sejong', 'location_id' => 158665, 'municipalities' => []],
            '6310000' => ['province' => 'Ulsan', 'location_id' => 155959, 'municipalities' => ['3700000' => ['name' => '남구', 'location_id' => 155973], '3710000' => ['name' => '동구', 'location_id' => 155984], '3720000' => ['name' => '북구', 'location_id' => 156002], '3730000' => ['name' => '울주군', 'location_id' => 155960], '3690000' => ['name' => '중구', 'location_id' => 155991]]],
            '6410000' => ['province' => 'Gyeonggi-do', 'location_id' => 156869, 'municipalities' => ['4160000' => ['name' => '가평군', 'location_id' => 157160], '3940000' => ['name' => '고양시', 'location_id' => 157042], '3970000' => ['name' => '과천시', 'location_id' => 157118], '3900000' => ['name' => '광명시', 'location_id' => 157100], '5540000' => ['name' => '광주시', 'location_id' => 157021], '3980000' => ['name' => '구리시', 'location_id' => 157125], '4020000' => ['name' => '군포시', 'location_id' => 157089], '4090000' => ['name' => '김포시', 'location_id' => 157032], '3990000' => ['name' => '남양주시', 'location_id' => 157005], '3920000' => ['name' => '동두천시', 'location_id' => 156908], '3860000' => ['name' => '부천시', 'location_id' => 156958], '3780000' => ['name' => '성남시', 'location_id' => 156921], '3740000' => ['name' => '수원시', 'location_id' => 156916], '4010000' => ['name' => '시흥시', 'location_id' => 156925], '3930000' => ['name' => '안산시', 'location_id' => 157086], '4080000' => ['name' => '안성시', 'location_id' => 157064], '3830000' => ['name' => '안양시', 'location_id' => 157061], '5590000' => ['name' => '양주시', 'location_id' => 157131], '4170000' => ['name' => '양평군', 'location_id' => 156886], '5700000' => ['name' => '여주시', 'location_id' => 156885], '4140000' => ['name' => '연천군', 'location_id' => 156874], '4000000' => ['name' => '오산시', 'location_id' => 157106], '4050000' => ['name' => '용인시', 'location_id' => 156870], '4030000' => ['name' => '의왕시', 'location_id' => 157113], '3820000' => ['name' => '의정부시', 'location_id' => 156899], '4070000' => ['name' => '이천시', 'location_id' => 157046], '4060000' => ['name' => '파주시', 'location_id' => 156991], '3910000' => ['name' => '평택시', 'location_id' => 156937], '5600000' => ['name' => '포천시', 'location_id' => 156976], '4040000' => ['name' => '하남시', 'location_id' => 157078], '5530000' => ['name' => '화성시', 'location_id' => 157139]]],
            '6420000' => ['province' => 'Gangwon', 'location_id' => 157238, 'municipalities' => ['4200000' => ['name' => '강릉시', 'location_id' => 157321], '4340000' => ['name' => '고성군', 'location_id' => 157315], '4210000' => ['name' => '동해시', 'location_id' => 157286], '4240000' => ['name' => '삼척시', 'location_id' => 157424], '4230000' => ['name' => '속초시', 'location_id' => 157297], '4320000' => ['name' => '양구군', 'location_id' => 157256], '4350000' => ['name' => '양양군', 'location_id' => 157249], '4270000' => ['name' => '영월군', 'location_id' => 157239], '4190000' => ['name' => '원주시', 'location_id' => 157262], '4330000' => ['name' => '인제군', 'location_id' => 157341], '4290000' => ['name' => '정선군', 'location_id' => 157399], '4300000' => ['name' => '철원군', 'location_id' => 157391], '4180000' => ['name' => '춘천시', 'location_id' => 157369], '4220000' => ['name' => '태백시', 'location_id' => 157415], '4280000' => ['name' => '평창군', 'location_id' => 157306], '4250000' => ['name' => '홍천군', 'location_id' => 157348], '4310000' => ['name' => '화천군', 'location_id' => 157409], '4260000' => ['name' => '횡성군', 'location_id' => 157359]]],
            '6430000' => ['province' => 'Chungbuk', 'location_id' => 157696, 'municipalities' => ['4460000' => ['name' => '괴산군', 'location_id' => 157750], '4480000' => ['name' => '단양군', 'location_id' => 157719], '4420000' => ['name' => '보은군', 'location_id' => 157728], '4440000' => ['name' => '영동군', 'location_id' => 157697], '4430000' => ['name' => '옥천군', 'location_id' => 157740], '4470000' => ['name' => '음성군', 'location_id' => 157709], '4400000' => ['name' => '제천시', 'location_id' => 157800], '5570000' => ['name' => '증평군', 'location_id' => 157818], '4450000' => ['name' => '진천군', 'location_id' => 157792], '5710000' => ['name' => '청주시', 'location_id' => 157787], '4390000' => ['name' => '충주시', 'location_id' => 157762]]],
            '6440000' => ['province' => 'Chungnam', 'location_id' => 157525, 'municipalities' => ['5580000' => ['name' => '계룡시', 'location_id' => 157691], '4500000' => ['name' => '공주시', 'location_id' => 157627], '4550000' => ['name' => '금산군', 'location_id' => 157616], '4540000' => ['name' => '논산시', 'location_id' => 157600], '5680000' => ['name' => '당진시', 'location_id' => 157539], '4510000' => ['name' => '보령시', 'location_id' => 157586], '4570000' => ['name' => '부여군', 'location_id' => 157569], '4530000' => ['name' => '서산시', 'location_id' => 157540], '4580000' => ['name' => '서천군', 'location_id' => 157555], '4520000' => ['name' => '아산시', 'location_id' => 157669], '4610000' => ['name' => '예산군', 'location_id' => 157526], '4490000' => ['name' => '천안시', 'location_id' => 157666], '4590000' => ['name' => '청양군', 'location_id' => 157655], '4620000' => ['name' => '태안군', 'location_id' => 157682], '4600000' => ['name' => '홍성군', 'location_id' => 157643]]],
            '6450000' => ['province' => 'Jeonbuk', 'location_id' => 158136, 'municipalities' => ['4780000' => ['name' => '고창군', 'location_id' => 158240], '4670000' => ['name' => '군산시', 'location_id' => 158194], '4710000' => ['name' => '김제시', 'location_id' => 158220], '4700000' => ['name' => '남원시', 'location_id' => 158163], '4740000' => ['name' => '무주군', 'location_id' => 158187], '4790000' => ['name' => '부안군', 'location_id' => 158149], '4770000' => ['name' => '순창군', 'location_id' => 158137], '4720000' => ['name' => '완주군', 'location_id' => 158344], '4680000' => ['name' => '익산시', 'location_id' => 158268], '4760000' => ['name' => '임실군', 'location_id' => 158255], '4750000' => ['name' => '장수군', 'location_id' => 158336], '4640000' => ['name' => '전주시', 'location_id' => 158297], '4690000' => ['name' => '정읍시', 'location_id' => 158300], '4730000' => ['name' => '진안군', 'location_id' => 158324]]],
            '6460000' => ['province' => 'Jeonnam', 'location_id' => 157821, 'municipalities' => ['4920000' => ['name' => '강진군', 'location_id' => 158054], '4880000' => ['name' => '고흥군', 'location_id' => 158037], '4860000' => ['name' => '곡성군', 'location_id' => 158025], '4840000' => ['name' => '광양시', 'location_id' => 158004], '4870000' => ['name' => '구례군', 'location_id' => 158017], '4830000' => ['name' => '나주시', 'location_id' => 157952], '4850000' => ['name' => '담양군', 'location_id' => 157887], '4800000' => ['name' => '목포시', 'location_id' => 157982], '4950000' => ['name' => '무안군', 'location_id' => 157972], '4890000' => ['name' => '보성군', 'location_id' => 157939], '4820000' => ['name' => '순천시', 'location_id' => 157900], '5010000' => ['name' => '신안군', 'location_id' => 157925], '4810000' => ['name' => '여수시', 'location_id' => 157846], '4970000' => ['name' => '영광군', 'location_id' => 157822], '4940000' => ['name' => '영암군', 'location_id' => 157834], '4990000' => ['name' => '완도군', 'location_id' => 157874], '4980000' => ['name' => '장성군', 'location_id' => 158113], '4910000' => ['name' => '장흥군', 'location_id' => 158125], '5000000' => ['name' => '진도군', 'location_id' => 158105], '4960000' => ['name' => '함평군', 'location_id' => 158080], '4930000' => ['name' => '해남군', 'location_id' => 158090], '4900000' => ['name' => '화순군', 'location_id' => 158066]]],
            '6470000' => ['province' => 'Gyeongbuk', 'location_id' => 156555, 'municipalities' => ['5130000' => ['name' => '경산시', 'location_id' => 156703], '5050000' => ['name' => '경주시', 'location_id' => 156596], '5200000' => ['name' => '고령군', 'location_id' => 156751], '5080000' => ['name' => '구미시', 'location_id' => 156727], '5140000' => ['name' => '군위군', 'location_id' => 156718], '5060000' => ['name' => '김천시', 'location_id' => 156827], '5120000' => ['name' => '문경시', 'location_id' => 156849], '5240000' => ['name' => '봉화군', 'location_id' => 156689], '5110000' => ['name' => '상주시', 'location_id' => 156664], '5210000' => ['name' => '성주군', 'location_id' => 156653], '5070000' => ['name' => '안동시', 'location_id' => 156795], '5180000' => ['name' => '영덕군', 'location_id' => 156556], '5170000' => ['name' => '영양군', 'location_id' => 156820], '5090000' => ['name' => '영주시', 'location_id' => 156760], '5100000' => ['name' => '영천시', 'location_id' => 156566], '5230000' => ['name' => '예천군', 'location_id' => 156583], '5260000' => ['name' => '울릉군', 'location_id' => 156619], '5250000' => ['name' => '울진군', 'location_id' => 156623], '5150000' => ['name' => '의성군', 'location_id' => 156634], '5190000' => ['name' => '청도군', 'location_id' => 156776], '5160000' => ['name' => '청송군', 'location_id' => 156860], '5220000' => ['name' => '칠곡군', 'location_id' => 156786], '5020000' => ['name' => '포항시', 'location_id' => 156700]]],
            '6480000' => ['province' => 'Gyeongnam', 'location_id' => 158400, 'municipalities' => ['5370000' => ['name' => '거제시', 'location_id' => 158484], '5470000' => ['name' => '거창군', 'location_id' => 158502], '5420000' => ['name' => '고성군', 'location_id' => 158469], '5350000' => ['name' => '김해시', 'location_id' => 158515], '5430000' => ['name' => '남해군', 'location_id' => 158441], '5360000' => ['name' => '밀양시', 'location_id' => 158452], '5340000' => ['name' => '사천시', 'location_id' => 158650], '5450000' => ['name' => '산청군', 'location_id' => 158429], '5380000' => ['name' => '양산시', 'location_id' => 158401], '5390000' => ['name' => '의령군', 'location_id' => 158415], '5310000' => ['name' => '진주시', 'location_id' => 158603], '5410000' => ['name' => '창녕군', 'location_id' => 158635], '5670000' => ['name' => '창원시', 'location_id' => 158629], '5330000' => ['name' => '통영시', 'location_id' => 158587], '5440000' => ['name' => '하동군', 'location_id' => 158573], '5400000' => ['name' => '함안군', 'location_id' => 158562], '5460000' => ['name' => '함양군', 'location_id' => 158550], '5480000' => ['name' => '합천군', 'location_id' => 158532]]],
            '6500000' => ['province' => 'Jeju', 'location_id' => 158358, 'municipalities' => ['6520000' => ['name' => '서귀포시', 'location_id' => 158359], '6510000' => ['name' => '제주시', 'location_id' => 158377]]],
        ];
    }
}
