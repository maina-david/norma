<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use DOMElement;
use GuzzleHttp\Psr7\Query;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: us-codebook
 * title: codebook.com
 * url: https://www.codebook.com/text-library
 */
class CodePublishing extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-codebook',
            'throttle_requests' => 100,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.codebook.com/text-library/',
                    ]),
                ],

                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.codebook.com/text-library/',
                    ]),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/www\.codebook\.com\/text-library/')) {
            $this->handleFremontCatalogue($pageCrawl);
            //            $this->handleCodePublishingCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/codepublishing\.com\//')) {
            $this->handleMeta($pageCrawl);
            $this->handleContent($pageCrawl);
            $this->captureToc($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/www\.codebook\.com\/text-library/')) {
            $this->fetchOrdinanceUpdateLinks($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/OT\.html\?_=\d+$/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/codepublishing\.com\//')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_for' => '#mainBodyInner',
                    'js_scenario' => [
                        'instructions' => [
                            ['evaluate' => 'document.querySelector("#saveForm").removeAttribute("target");document.querySelectorAll(".tocItem input[id^=\'tocItemFremont\']").forEach((el) => el.click())'],
                            ['wait' => 3000],
                        ],
                    ],
                ],
            ]);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/OT\.html\?_=\d+$/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return array<string>
     */
    //    protected function fetchStates(PageCrawl $pageCrawl): array
    //    {
    //        $states = [];
    //        $response = Http::get('https://www.codebook.com/listing/');
    //
    //        if ($response->successful()) {
    //            $anchors = $pageCrawl->domCrawler->filter('.dropdown .dropdown-content a');
    //
    //            /** @var DOMElement $anchor */
    //            foreach ($anchors as $anchor) {
    //                $href = $anchor->getAttribute('href');
    //                $states[] = Str::of($href)->after('#');
    //            }
    //        }
    //
    //        return $states;
    //    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    //    protected function handleCodePublishingCatalogue(PageCrawl $pageCrawl): void
    //    {
    //        $this->arachno->eachX($pageCrawl, '//*[@id="states"]/div/div/div/div[1]/a[contains(@href, "codepublishing")]',
    //            function (DOMElement $element) use ($pageCrawl) {
    //                $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://www.codebook.com');
    //
    //                /** @var DOMElement $title */
    //                $title = $crawler->filterXPath('.//a')->getNode(0);
    //                $title = preg_replace('/\n+/', '', trim($title->textContent));
    //
    //                $url = $crawler->filterXPath('.//a')->link()->getUri();
    //                $city = Str::of($url)->beforeLast('/');
    //                $uniqueId = Str::of($url)->after('.com/');
    //
    //                $catDoc = new CatalogueDoc([
    //                    'title' => $title,
    //                    'source_unique_id' => $uniqueId,
    //                    'primary_location_id' => $this->matchCities($city),
    //                    'language_code' => 'eng',
    //                    'view_url' => $url,
    //                    'start_url' => $url,
    //                ]);
    //
    //                $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
    //            });
    //    }

    /**
     * @param string $city
     *
     * @return string
     */
    protected function matchCities(string $city): string
    {
        return match (strtolower($city)) {
            'santacruzcounty' => '2628',
            'burbank' => '69025',
            'folsom' => '69151',
            'fremont' => '88731',
            'newportbeach' => '69104',
            'santaclara' => '69209',
            'santaclarita' => '21597',
            'torrance' => '69053',
            'walnutcreek' => '68939',
            'concord' => '68946',
            'dublin' => '68918',
            'emeryville' => '68919',
            'capitola' => '69221',
            'southlaketahoe' => '186510',
            'SantaCruz' => '69220',
            default => '2338'
        };
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleFremontCatalogue(PageCrawl $pageCrawl): void
    {
        $catDoc = new CatalogueDoc([
            'title' => 'Fremont Municipal Code',
            'source_unique_id' => 'CA/Fremont',
            'primary_location_id' => 88731,
            'language_code' => 'eng',
            'view_url' => 'https://www.codepublishing.com/CA/Fremont',
            'start_url' => 'https://www.codepublishing.com/CA/Fremont',
        ]);

        $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return \App\Models\Corpus\Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        /** @var DOMElement $title */
        $title = $pageCrawl->domCrawler->filter('h1#cityname')->getNode(0);
        $title = $title->textContent;

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catalogueDoc->primary_location_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'code');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    protected function extractContentFromTitles(PageCrawl $pageCrawl): string
    {
        //        $url = $pageCrawl->pageUrl->url;
        //        $uri = Str::of($url)->after('.com/')->beforeLast('/');

        $pages = [];

        $nodes = $pageCrawl->domCrawler->filter('#archiveTOC > .tocItem')->getIterator();
        foreach ($nodes as $node) {
            /** @var DOMElement $node */
            $params = [
                'Print/Save Selections' => 'Print/Save Selections',
                'code' => [],
            ];
            $crawler = new Crawler($node);
            /** @var DOMElement $rootEl */
            $rootEl = $crawler->filter('input[name=code]')->getNode(0);
            $params['code'][] = $rootEl->getAttribute('value');

            /** @var DOMElement $children */
            $children = $node->nextElementSibling;
            $children = (new Crawler($children))->filter('input[name=code]')->getIterator();

            foreach ($children as $child) {
                /** @var DOMElement $child */
                $params['code'][] = $child->getAttribute('value');
            }

            $params['code'] = collect($params['code'])->unique()->values()->all();

            $response = Http::timeout(300)->withBody(Query::build($params), 'application/x-www-form-urlencoded')
                ->post('https://www.codepublishing.com/CA/Fremont/cgi/menuCompile.pl');

            $page = new Crawler($response->body());
            $pages[] = $page->filter('#mainContent')->html();
            sleep(5);
        }
        $skeleton = Http::asForm()
            ->post('https://www.codepublishing.com/CA/Fremont/cgi/menuCompile.pl', [
                'Print/Save Selections' => 'Print/Save Selections',
            ])->body();

        $main = implode("\n", $pages);

        /** @var string $skeleton */
        $skeleton = preg_replace('/<div id="mainContent">(\n|\s)+<\/div>/', '<div id="mainContent">__main_content_here__</div>', $skeleton);

        return str_replace('__main_content_here__', $main, $skeleton);
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleContent(PageCrawl $pageCrawl): void
    {
        $content = $this->extractContentFromTitles($pageCrawl);
        $pageCrawl->domCrawler->clear();
        $pageCrawl->domCrawler->addHtmlContent($content);

        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#mainContent',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"]',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'p[class*="TOC"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.digTitle',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.digCH',
            ],
        ]);

        $preStore = function (Crawler $crawler) {
            $extHrefs = $crawler->filter('a[href*="#"]');

            /** @var DOMElement $extHref */
            foreach ($extHrefs as $extHref) {
                $extHref->removeAttribute('href');
            }

            $existing = [];
            $elements = $crawler->filter('h1')->getIterator();

            foreach ($elements as $el) {
                /** @var DOMElement $el */
                /** @var DOMElement|null $anchor */
                $anchor = (new Crawler($el))->filter('a[name]')->getNode(0);

                if ($anchor) {
                    $name = $anchor->getAttribute('name');

                    if (in_array($name, $existing)) {
                        $el->remove();
                        continue;
                    }

                    $existing[] = $name;
                }
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function captureToc(PageCrawl $pageCrawl): void
    {
        $extractLink = function ($nodeDTO) use ($pageCrawl) {
            $id = $nodeDTO['node']->getAttribute('id');
            $url = $pageCrawl->getFinalRedirectedUrl();

            return "{$url}#{$id}";
        };

        $extractUniqueId = function ($nodeDTO) {
            return $nodeDTO['node']?->getAttribute('id');
        };

        $label = function ($nodeDTO) {
            $text = html_to_text($nodeDTO['node']->ownerDocument->saveHTML($nodeDTO['node']->parentNode));

            return preg_replace('/\n+/', ' ', $text);
        };

        $existing = [];
        $criteria = function ($nodeDTO) use (&$existing) {
            $isAnchor = strtolower($nodeDTO['node']->nodeName) === 'a';
            $name = $nodeDTO['node']->getAttribute('name');

            if (!$isAnchor || in_array($name, $existing)) {
                return false;
            }

            $parentClass = $nodeDTO['node']->parentNode->getAttribute('class');

            $hasMatched = in_array($parentClass, ['Title', 'CH', 'Cite']);

            if ($hasMatched) {
                $existing[] = $name;
            }

            return $hasMatched;
        };

        $determineDepth = function ($nodeDTO) {
            $elName = strtolower($nodeDTO['node']?->parentNode->nodeName);

            return match ($elName) {
                'h1' => 1,
                'h2' => 2,
                default => 3
            };
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            '#mainContent',
            $criteria,
            $label,
            $extractLink,
            $extractUniqueId,
            $determineDepth
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function fetchOrdinanceUpdateLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[@id="states"]/div/div/div/div[1]/a[contains(@href, "codepublishing")]',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://www.codepublishing.com');

                $cities = ['SantaCruzCounty', 'Burbank', 'Folsom', 'Fremont', 'NewportBeach', 'SantaClara',
                    'SantaClarita', 'Torrance', 'WalnutCreek', 'Concord', 'Dublin', 'Emeryville', 'Capitola',
                    'SouthLakeTahoe', 'SantaCruz'];

                $urlValues = explode('/', $crawler->filterXPath('.//a')->link()->getUri());
                $match = array_values(array_intersect($urlValues, $cities))[0] ?? null;

                if (!$match) {
                    return;
                }

                $url = $crawler->filterXPath('.//a')->link()->getUri();

                $uri = Str::of($url)->after('.com/');
                $city = Str::of($uri)->beforeLast('/');
                $city = Str::of($city)->after('/');

                if (str_contains(strtolower($url), 'concord')) {
                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "https://www.codepublishing.com/{$uri}html/{$city}OT/{$city}OTB.html?_=1714032571456"]));

                    return;
                }

                if (str_contains(strtolower($url), 'walnutcreek')) {
                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "https://www.codepublishing.com/{$uri}html/{$city}OT2.html?_=1714032571456"]));

                    return;
                }

                if (str_contains(strtolower($url), 'southlaketahoe')) {
                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "https://www.codepublishing.com/{$uri}html/{$city}OT/{$city}OT2.html?_=1714032571456"]));

                    return;
                }

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "https://www.codepublishing.com/{$uri}html/{$city}OT.html?_=1714032571456"]));
            });
    }

    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//table[contains(@class, "ordAlert")]//tr',
            function (DOMElement $row) use ($pageCrawl) {
                $docMetaDto = new DocMetaDto();
                $crawler = new Crawler($row, 'https://www.codepublishing.com', $pageCrawl->pageUrl->url);

                if (!$crawler->filterXpath('.//td//p[@class="ordtab"]//a')->getNode(0)) {
                    return;
                }

                $url = $crawler->filterXpath('.//td//p[@class="ordtab"]//a')->link()->getUri();
                $uniqueId = Str::of($url)->after('.com/')->before('.pdf');

                $city = Str::of($pageCrawl->pageUrl->url)->after('.com/')->before('/html');
                $city = Str::of($city)->after('/');

                /** @var DOMElement $title */
                $title = $crawler->filterXPath('.//td[3]')->getNode(0) ?? $crawler->filterXPath('.//td[2]')->getNode(0);
                $title = $title->textContent;
                $title = strlen($title) > 200
                    ? $city . ' ordinance ' . Str::of($url)->afterLast('/')->before('.pdf')
                    : $title;

                $docMetaDto->title = $title;
                $docMetaDto->source_unique_id = "updates_{$uniqueId}";
                $docMetaDto->source_url = $url;
                $docMetaDto->download_url = $url;
                $docMetaDto->work_type = 'ordinance';
                $docMetaDto->primary_location = $this->matchCities(strtolower($city));
                $docMetaDto->language_code = 'eng';

                $link = new UrlFrontierLink(['url' => $url]);
                $link->_metaDto = $docMetaDto;
                $this->arachno->followLink($pageCrawl, $link, true);
            });
    }
}
