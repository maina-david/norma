<?php

namespace App\Services\Arachno\Crawlers\Sources\Fr;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: fr-legifrance
 * title: legifrance.gouv.fr
 * url: https://www.legifrance.gouv.fr/jorf/jo.
 */
class Legifrance extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'fr-legifrance',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.legifrance.gouv.fr/jorf/jo',
                    ]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.legifrance.gouv.fr/liste/code?etatTexte=VIGUEUR&etatTexte=VIGUEUR_DIFF',
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
            && $this->arachno->matchUrl($pageCrawl, '/liste\/code/')) {
            $this->fetchInitialLinks($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/legifrance\.gouv\.fr\/codes\/texte_lc/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/legifrance\.gouv\.fr\/codes\/section_lc/')) {
            $this->handleMeta($pageCrawl);
            $this->handleTocLinks($pageCrawl);
            $this->handleCapture($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/jorf\/jo/')) {
            $this->handleForUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\/jorf\/id/')) {
            $this->getDownloadLink($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\/JOE_TEXTE$/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\/JOE_TEXTE$/')) {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => ['render_js' => false, 'premium_proxy' => true],
                ]);
            } else {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => ['render_js' => true],
                ]);
            }
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => ['render_js' => false],
            ]);
        }
    }

    protected function fetchInitialLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.code-list li', function (DOMElement $element) use ($pageCrawl) {
            $crawler = new DomCrawler($element, $pageCrawl->pageUrl->url, 'https://www.legifrance.gouv.fr');

            $link = $crawler->filterXPath('.//h2[@class="title-result-item-code"]/a')->link()->getUri();

            $downloadLink = $crawler->filterXPath('.//div[@class="picto-download"]/a')->link()->getUri();
            $downloadLink = Str::of($downloadLink)->after('id=')->before('.pdf');
            $downloadLink = "https://www.legifrance.gouv.fr/download/file/pdf/{$downloadLink}.pdf/LEGI";

            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link, 'anchor_text' => $downloadLink]));
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'a.title-link', function (DOMElement $element) use ($pageCrawl) {
            $crawler = new DomCrawler($element, $pageCrawl->pageUrl->url, 'https://www.legifrance.gouv.fr');
            if (str_starts_with(strtolower($element->textContent), 'titre') || str_starts_with(strtolower($element->textContent), 'annexes')) {
                // Annexe 6.1
                /** @var DOMElement|null $node */
                $node = $crawler->filterXPath('.//a')->getNode(0);
                $id = $node?->getAttribute('id') ?? '';
                $id = Str::of($id)->after('id');

                $link = $crawler->filterXPath('.//a')->link()->getUri();
                $title = $node?->textContent ?? '';

                $catDoc = new CatalogueDoc([
                    'title' => trim($title),
                    'start_url' => $link,
                    'view_url' => $link,
                    'source_unique_id' => $id,
                    'language_code' => 'fra',
                    'primary_location_id' => 42316,
                ]);

                $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            }
        });
    }

    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        $printTitles = $pageCrawl->domCrawler->filter('.print-title-summary');
        $longTitle = '';
        $topTitle = $pageCrawl->domCrawler->filter('.summary-header.print-invisible a')->getNode(0);

        foreach ($printTitles as $printTitle) {
            $longTitle .= ' ' . $printTitle->textContent;
        }

        $catalogueDoc->update(['title' => $topTitle?->textContent . ' ' . $longTitle]);

        $downloadLink = Str::of((string) $catalogueDoc->start_url)->after('section_lc/')->before('/');
        $downloadLink = "https://www.legifrance.gouv.fr/download/file/pdf/{$downloadLink}.pdf/LEGI";

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'fra');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 42316);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadLink);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'code');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCapture(PageCrawl $pageCrawl): void
    {
        /** @var ContentCache $contentCache */
        $contentCache = $pageCrawl->contentCache;
        $pageCrawl->domCrawler->clear();

        $pageCrawl->domCrawler->addHtmlContent($contentCache->response_body ?? '');
        $this->arachno->each($pageCrawl, 'meta[http-equiv="Content-Type"]', fn ($node) => $node->remove());

        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#liste-article-noeud',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'button.title-link',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.tabs-secondary.tabs-main button',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.hidden-element',
            ],
        ]);

        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@rel,"stylesheet")]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//style',
            ],
        ]);

        $preStore = function (Crawler $crawler) {
            $anchors = $crawler->filter('a');

            /** @var DOMElement $anchor */
            foreach ($anchors as $anchor) {
                if (!$anchor->getAttribute('id') || !str_contains(strtolower($anchor->getAttribute('href')), 'pdf')) {
                    $anchor->removeAttribute('href');
                    $anchor->removeAttribute('target');
                }
            }

            $dataAnchors = $crawler->filter('span[data-anchor]');

            /** @var DOMElement $dataAnchor */
            foreach ($dataAnchors as $dataAnchor) {
                $dataAnchor->setAttribute('id', $dataAnchor->getAttribute('data-anchor'));
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
        $extractLink = function ($nodeDTO) use ($pageCrawl) {
            $id = $nodeDTO['node']?->getAttribute('id');

            return "{$pageCrawl->pageUrl->url}#{$id}";
        };

        $extractUniqueId = function ($nodeDTO) {
            return $nodeDTO['node']?->getAttribute('id');
        };

        $criteria = function ($nodeDTO) {
            $id = $nodeDTO['node']->getAttribute('id');

            return strtolower($nodeDTO['node']->nodeName) === 'a' && $id;
        };

        $determineDepth = function ($nodeDTO) {
            $nodeText = $nodeDTO['node']->textContent;

            return match (explode(' ', strtolower($nodeText))[0]) {
                'chapitre, annexe' => 1,
                'section' => 2,
                'sous-section' => 3,
                'article' => 4,
                default => 1
            };
        };

        $this->arachno->captureTocCss(
            $pageCrawl,
            '#liste-article-noeud',
            $criteria,
            null,
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
    protected function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.list-num li', function (DOMElement $entry) use ($pageCrawl) {
            $domCrawler = new DomCrawler($entry);

            /** @var DOMElement $anchor */
            $anchor = $domCrawler->filterXPath('.//a')->getNode(0);
            $href = $anchor->getAttribute('href');

            $id = str_replace('id', '', $anchor->getAttribute('id'));

            $href = 'https://www.legifrance.gouv.fr' . $href;
            $title = $domCrawler->filterXPath('.//a')->getNode(0)?->textContent;
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = $id;
            $docMeta->title = $title;
            $docMeta->language_code = 'fra';
            $docMeta->source_url = $href;
            $docMeta->primary_location = '42316';

            $type = $domCrawler->filterXPath('//ol[@class="list-num"]//@data-nature')->getNode(0)?->textContent ?? '';
            $workType = match (strtolower($type)) {
                'decret' => 'decree',
                'arrete' => 'order',
                'decision' => 'decision',
                'deliberation' => 'agreement',
                'loi' => 'act',
                'rapport' => 'measures',
                'ordonnance' => 'ordinance',
                default => 'notice'
            };

            $docMeta->work_type = $workType;

            $l = new UrlFrontierLink(['url' => $href]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    protected function getDownloadLink(PageCrawl $pageCrawl): Doc
    {
        $url = $pageCrawl->getFinalRedirectedUrl();

        $domCrawler = $pageCrawl->domCrawler;
        $date = $domCrawler->filterXPath('//span[@class="word-break-all"]')->getNode(0)?->textContent;

        $workDate = $date ? Carbon::parse(Str::of($date)->match('/[0-9]+\/[0-9]+\/[0-9]+/')) : null;
        $summary = $domCrawler->filter('.summary-preface p')->getNode(0)?->textContent ?? null;

        /** @var DOMElement|null $pdfUrlNode */
        $pdfUrlNode = $domCrawler->filterXPath('//*[contains(@class, "doc-download")]')->getNode(0);
        $pdfUrl = $pdfUrlNode?->getAttribute('href');
        $downloadUrl = null;

        if ($pdfUrl && Str::contains($pdfUrl, '?id=')) {
            $pdfUrl = explode('?id=', $pdfUrl);
            $downloadUrl = 'https://www.legifrance.gouv.fr/download/file/' . $pdfUrl[1] . '/JOE_TEXTE';
        }

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $summary);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);

        if ($downloadUrl) {
            $l = new UrlFrontierLink(['url' => $downloadUrl]);
            $this->arachno->followLink($pageCrawl, $l, true);
        }

        return $doc;
    }
}
