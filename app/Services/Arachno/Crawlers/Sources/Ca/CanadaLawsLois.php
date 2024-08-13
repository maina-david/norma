<?php

namespace App\Services\Arachno\Crawlers\Sources\Ca;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: ca-laws-lois
 * title: Justice Laws Website (laws-lois.justice.gc.ca)
 * url: https://laws-lois.justice.gc.ca/eng/
 */
class CanadaLawsLois extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ca-laws-lois',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://laws-lois.justice.gc.ca/eng/acts/']),
                    new UrlFrontierLink(['url' => 'https://laws-lois.justice.gc.ca/eng/regulations/']),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if (!$this->arachno->matchCSS($pageCrawl, '.contentBlock')) {
                $this->followInitialLinks($pageCrawl);
            }
            if ($this->arachno->matchCSS($pageCrawl, '.contentBlock')) {
                $this->handleCatalogue($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/index\.html/')) {
                //                $this->arachno->followLinksCSS($pageCrawl, 'div[id="printAll"] li a[href*="FullText"]');

                $this->handleMeta($pageCrawl);

                if ($this->arachno->matchCSS($pageCrawl, '.TocIndent')) {
                    $this->handleTocLinks($pageCrawl);
                }
            }

            if ($this->arachno->matchUrl($pageCrawl, '/\.html/') && !$this->arachno->matchUrl($pageCrawl, '/index\.html/')) {
                $this->fetchContent($pageCrawl);
            }
        }
    }

    protected function followInitialLinks(PageCrawl $pageCrawl): void
    {
        $alphaLinks = $pageCrawl->domCrawler->filter('#alphaList div a:not([href*="#"])');

        foreach ($alphaLinks as $alphaLink) {
            $crawler = new Crawler($alphaLink, 'https://laws-lois.justice.gc.ca/', $pageCrawl->pageUrl->url);
            $link = $crawler->filterXPath('.//a')->link()->getUri();
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]), true);
        }
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.contentBlock li .objTitle', function (DOMElement $element) use ($pageCrawl) {
            $crawler = new Crawler($element, 'https://laws-lois.justice.gc.ca/', $pageCrawl->pageUrl->url);
            $title = $crawler->filterXPath('.//a[@class="TocTitle"]')->getNode(0);
            $title = str_replace('\n', '', $title->textContent ?? '');

            if (str_contains(strtolower($title), 'repealed')) {
                return;
            }

            $url = $crawler->filterXPath('.//a[@class="TocTitle"]')->link()->getUri();
            $uniqueId = Str::of($url)->after('.ca/')->before('/index');

            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'source_unique_id' => $uniqueId,
                'start_url' => $url,
                'view_url' => $url,
                'primary_location_id' => 388,
                'language_code' => 'eng',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });
    }

    public function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        $pdfLink = $pageCrawl->domCrawler->filter('.legisHeader div li a[href$="pdf"]')->getNode(0);
        if ($pdfLink) {
            $crawler = new Crawler($pdfLink, 'https://laws-lois.justice.gc.ca/', $pageCrawl->pageUrl->url);
            $downloadLink = $crawler->filterXPath('.//a')->link()->getUri();
        }
        //        $url = $pageCrawl->domCrawler->filter('div[id="printAll"] li a[href*="FullText"]')?->link()?->getUri() ?? '';
        $workType = str_contains(strtolower($catalogueDoc->start_url ?? ''), 'regulation') ? 'regulation' : 'act';

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 388);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadLink ?? null);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    protected function fetchContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.docContents',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@rel,"stylesheet")]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.btn-group.btn-group-justified',
            ],
        ]);

        $preStore = function (Crawler $crawler) {
            $externalLinks = $crawler->filter('a:not([href="pdf"])');

            /** @var DOMElement $externalLink */
            foreach ($externalLinks as $externalLink) {
                $externalLink->removeAttribute('href');
            }

            return $crawler;
        };

        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
            preStoreCallable: $preStore
        );
    }

    protected function handleTocLinks(PageCrawl $pageCrawl): void
    {
        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']?->getAttribute('href');

            return Str::of($href)->after('#');
        };

        $extractLabel = function ($nodeDTO) {
            /** @var DOMElement $node */
            $node = $nodeDTO['node'];
            $text = $node->parentNode?->textContent;

            $hasChildren = strtolower($node->nextElementSibling?->nodeName ?? '') === 'ul';
            $replace = $hasChildren ? $node->nextElementSibling?->textContent : null;

            /** @var string $text */
            $text = $replace ? str_replace($replace, '', $text ?? '') : $text;

            return trim($text);
        };

        $extracted = [];

        $criteria = function ($nodeDTO) use (&$extracted) {
            $titleHref = $nodeDTO['node']->getAttribute('href');

            $exists = in_array($titleHref, $extracted);

            if ($matched = !empty($titleHref) && !$exists) {
                $extracted[] = $titleHref;
            }

            return $matched;
        };

        $this->arachno->captureTocCss(
            $pageCrawl,
            'nav .TocIndent',
            $criteria,
            $extractLabel,
            null,
            $extractUniqueId,
        );
    }
}
