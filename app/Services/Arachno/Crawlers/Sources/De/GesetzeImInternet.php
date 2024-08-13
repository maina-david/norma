<?php

namespace App\Services\Arachno\Crawlers\Sources\De;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: de-gesetze-im-internet
 * title: Gesetze-im-internet.de
 * url: https://www.gesetze-im-internet.de/.
 */
class GesetzeImInternet extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/gesetze\-im\-internet\.de\/Teilliste/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\/index\.html$/')) {
                $this->handleMeta($pageCrawl);
                $this->handleTocLinks($pageCrawl);
            }
            if ($this->arachno->matchXpath($pageCrawl, '//*[@id="container"]//*[@class="jnheader"]')
                && !$this->arachno->matchUrl($pageCrawl, '/\/index\.html$/')
                && !$this->arachno->matchUrl($pageCrawl, '/gesetze\-im\-internet\.de\/Teilliste/')) {
                $this->capturePageContent($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsSearch($pageCrawl)) {
            $this->handleSearch($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[@id="level2"]//*[@id="container"]//*[@id="paddingLR12"]//*[local-name()="p"]//*[local-name()="a"][1]', function (DOMElement $link) use ($pageCrawl) {
            $pCrawler = new DomCrawler($link->parentNode ?? '');
            $href = $link->getAttribute('href');
            $id = (string) Str::of($href)
                ->after('./')
                ->before('/');
            $abbr = trim($link->textContent);
            $title = trim($pCrawler->filterXPath('//text()[2]')->getNode(0)?->textContent ?? '');
            $fullTitle = implode(' - ', [$abbr, $title]);

            $url = 'https://www.gesetze-im-internet.de' . trim($href, '.');
            $catDoc = new CatalogueDoc([
                'title' => trim($fullTitle),
                'start_url' => $url,
                'view_url' => $url,
                'source_unique_id' => $id,
                'language_code' => 'deu',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });
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
            ->match('/www\.gesetze\-im\-internet\.de\/(.*)\/index\.html$/');

        $dom = $pageCrawl->domCrawler;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'deu');
        $fullTitle = (string) Str::of($dom->filterXPath('//head//meta[@name="htdig-keywords"]/@content')->getNode(0)?->textContent ?? '')
            ->replace('nichtamtliches Inhaltsverzeichnis - ', '')
            ->trim();
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $fullTitle);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '42320');

        $title = $dom->filter('#container h1.headline')->getNode(0)->textContent ?? '';
        $workType = str_starts_with(strtolower($title), 'verordnung') ? 'regulation' : 'act';
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) $workType);

        /** @var DOMElement */
        $htmlLink = $dom->filter('#container h2.headline a[href*=".html"]')->getNode(0);
        $htmlHref = $htmlLink->getAttribute('href');
        /** @var DomCrawler|null */
        $htmlPageDom = $this->arachno->fetchLink($pageCrawl, $htmlHref);
        if ($htmlPageDom) {
            $date = $htmlPageDom->filterXPath('//*[@class="jnnorm" and @title="Rahmen"]//*[@class="jnheader"]//p[contains(text(), "Ausfertigungsdatum")]')->getNode(0)?->textContent ?? '';
            $date = (string) Str::of($date)
                ->replace('Ausfertigungsdatum: ', '');
            $workDate = Carbon::createFromFormat('d.m.Y', $date);
            if ($workDate) {
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate->format('Y-m-d'));
            }
        }

        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleTocLinks(PageCrawl $pageCrawl): void
    {
        $determineDepth = function ($nodeDto) {
            return match ($nodeDto['node']->parentNode->getAttribute('colspan')) {
                '3' => 1,
                '2' => 2,
                '', null => 3,
                default => 1,
            };
        };
        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']?->getAttribute('href');
            // in this case it's a link to a part of the full page, and it might not be unique
            if (str_contains($href, '#')) {
                $href .= '_' . sha1($nodeDTO['node']?->textContent ?? '');
            }

            return $href;
        };

        $this->arachno->captureTocXpath(
            $pageCrawl,
            '//*[@id="level2"]//*[@id="container"]/*[@id="paddingLR12"]/table',
            null,
            null,
            null,
            $extractUniqueId,
            $determineDepth,
        );
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
                'type' => DomQueryType::XPATH,
                'query' => '//*[@id="container"]//*[@id="paddingLR12"]',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[not(@media) or @media="screen"]',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//*[@id="paddingLR12"]//*[text()="Nichtamtliches Inhaltsverzeichnis"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//*[@id="paddingLR12"]/div[@class="jnheader"]/h1/text()[1]', // removes the work heading from each section
            ],
        ]);
        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleSearch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/Teilliste/')) {
            $this->arachno->followLinksXpath($pageCrawl, '//*[@id="level2"]//*[@id="container"]//*[@id="paddingLR12"]//*[local-name()="p"]//*[local-name()="a"][1]');
        } elseif ($this->arachno->matchUrl($pageCrawl, '/index\.html/')) {
            $this->arachno->followLinksXpath($pageCrawl, '//*[@id="level2"]//*[@id="container"]/*[@id="paddingLR12"]/table//a[@href]');
        } else {
            $this->arachno->indexForSearch(
                $pageCrawl,
                contentQuery: new DomQuery(['query' => '#container', 'type' => DomQueryType::CSS]),
                primaryLocationId: 42320,
                languageCode: 'deu',
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        $startUrls = [
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_A.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_B.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_C.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_D.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_E.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_F.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_G.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_H.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_I.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_J.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_K.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_L.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_M.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_N.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_O.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_P.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_Q.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_R.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_S.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_T.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_U.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_V.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_W.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_X.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_Y.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_Z.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_9.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_8.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_7.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_6.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_5.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_4.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_3.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_2.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.gesetze-im-internet.de/Teilliste_1.html',
            ]),
        ];

        return [
            'slug' => 'de-gesetze-im-internet',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => $startUrls,
                'type_' . CrawlType::SEARCH->value => $startUrls,
            ],
        ];
    }
}
