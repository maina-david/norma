<?php

namespace App\Services\Arachno\Crawlers\Sources\Tr;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Support\DomClosest;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 *
 * slug: tr-resmigazete
 * title: Presidency of the Republic of Turkey OFFICIAL NEWSPAPER
 * url: https://resmigazete.gov.tr/
 */
class TurkeyOfficialGazette extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'tr-resmigazete',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://resmigazete.gov.tr/',
                    ]),
                ],
            ],
        ];
    }

    protected function updatePageEncoding(PageCrawl $pageCrawl): void
    {
        $encoding = $pageCrawl->domCrawler->getNode(0)?->ownerDocument->encoding ?? 'UTF-8';
        $pageCrawl->domCrawler->clear();
        $pageCrawl->domCrawler->addHtmlContent($pageCrawl->contentCache->response_body ?? '', $encoding);
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/resmigazete\.gov\.tr/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/htm/')) {
            $this->updatePageEncoding($pageCrawl);
            $this->captureHtmlContent($pageCrawl);
        }
    }

    //    public function preFetch(PageCrawl $pageCrawl): void
    //    {
    //        if ($this->arachno->crawlIsForUpdates($pageCrawl)
    //            && $this->arachno->matchUrl($pageCrawl, '/resmigazete\.gov\.tr/')) {
    //            $pageCrawl->setProxySettings([
    //                'provider' => 'scraping_bee',
    //                'options' => [
    //                    'render_js' => false,
    //                    'timeout' => '5000',
    //                ],
    //            ]);
    //        }
    //    }

    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $docMeta = new DocMetaDto();
        $links = $this->getRelevantLinks($pageCrawl);
        foreach ($links as $link) {
            $title = $link['title'];
            $url = $link['target'];
            $uniqueId = Str::of($url)->after('gov.tr/')->before('.');

            $docMeta->title = $title;
            $docMeta->source_unique_id = $uniqueId;
            $docMeta->source_url = $url;
            $docMeta->language_code = 'tur';
            $docMeta->primary_location = '42387';
            $docMeta->work_type = 'gazette';

            $lnk = new UrlFrontierLink(['url' => $docMeta->source_url]);
            $lnk->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $lnk);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return array<int, array<string, string>>
     */
    protected function getRelevantLinks(PageCrawl $pageCrawl): array
    {
        $includedTitles = [
            'YASAMA BÖLÜMÜ', 'YÜRÜTME VE İDARE BÖLÜMÜ',
        ];
        $excludedSubtitles = [
            'MİLLETLERARASI ANDLAŞMALAR', 'ATAMA KARARLARI', 'KURUL KARARI', 'CUMHURBAŞKANLIĞINA VEKÂLET ETME İŞLEMİ', 'TBMM KARARI', 'ATAMA KARARI,',
        ];
        $shouldEvaluate = ['html-title', 'html-subtitle', 'fihrist-item'];
        $captureTitle = false;
        $captureSubtitle = false;
        $links = [];

        $node = $pageCrawl->domCrawler->filter('#html-content')->getNode(0)?->firstChild;

        while ($node) {
            if (!method_exists($node, 'getAttribute')) {
                $node = $node->nextSibling;
                continue;
            }

            $classes = explode(' ', $node->getAttribute('class'));
            $match = array_values(array_intersect($classes, $shouldEvaluate))[0] ?? null;

            if (!$match) {
                $node = $node->nextSibling;
                continue;
            }

            if ($match === 'html-title') {
                $captureTitle = in_array(trim($node->textContent), $includedTitles);
                $node = $node->nextSibling;
                continue;
            }

            // check if we are capturing the title, if not, keep moving till the next title is evaluated.
            if (!$captureTitle) {
                $node = $node->nextSibling;
                continue;
            }

            if ($match === 'html-subtitle') {
                $captureSubtitle = !in_array(trim($node->textContent), $excludedSubtitles);
                $node = $node->nextSibling;
                continue;
            }

            // if we are not capturing the subtitles, lets move till we get to the next subtitle.
            if (!$captureSubtitle) {
                $node = $node->nextSibling;
                continue;
            }

            // if its a link, extract the content
            if ($match === 'fihrist-item') {
                /** @var DOMElement|null $link */
                $link = (new Crawler($node))->filter('a')->getNode(0);

                if ($link) {
                    $links[] = [
                        'title' => $node->textContent,
                        'target' => $link->getAttribute('href'),
                    ];
                }
            }

            $node = $node->nextSibling;
        }

        return $links;
    }

    protected function captureHtmlContent(PageCrawl $pageCrawl): void
    {
        /** @var ContentCache $contentCache */
        $contentCache = $pageCrawl->contentCache;
        $contentCache = str_replace('"text/html; charset=Windows-1254"', '"text/html;charset=UTF-8"', $contentCache->response_body ?? '');
        $contentCache = str_replace('"text/html;charset=Windows-1254"', '"text/html;charset=UTF-8"', $contentCache);
        $contentCache = mb_convert_encoding($contentCache, 'utf8', 'Windows-1254');

        $pageCrawl->domCrawler->clear();
        /** @var string $contentCache */
        $pageCrawl->domCrawler->addHtmlContent($contentCache);

        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.Section1 table table.MsoNormalTable',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//style',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"]',
            ],
        ]);

        $preStore = function (Crawler $crawler) use ($pageCrawl) {
            $elem = $crawler->filter('tr td p.MsoNormal');
            foreach ($elem as $el) {
                if (str_contains($el->textContent, 'Resmî Gazete')) {
                    $closestCl = app(DomClosest::class);
                    $topTr = $closestCl->closest($el, 'tr');
                    /** @var DOMElement $topTr */
                    $topTr = $topTr?->getNode(0);
                    $topTr->remove();
                }
            }

            $tableRows = $crawler->filter('tr');

            /** @var DOMElement $tableRow */
            foreach ($tableRows as $tableRow) {
                $tableRow->setAttribute('style', 'border-color: transparent !important;');
            }

            $tableDts = $crawler->filter('tr td');

            /** @var DOMElement $tableDt */
            foreach ($tableDts as $tableDt) {
                $tableDt->setAttribute('style', 'border-color: transparent !important;');
            }

            /** @var DOMElement $table */
            $table = $crawler->filter('table')->getNode(0);
            $table->setAttribute('style', 'width:100%');

            if ($crawler->filter('a[href*="pdf"]')->count() > 0) {
                foreach ($crawler->filter('a[href*="pdf"]') as $pdfLink) {
                    $url = $pageCrawl->pageUrl->url;
                    $url = Str::of($url)->beforeLast('/');

                    /** @var DomElement $pdfLink */
                    $fullUrl = str_contains($pdfLink->getAttribute('href'), 'https://www.resmigazete.gov.tr/')
                        ? $pdfLink->getAttribute('href')
                        : "{$url}/{$pdfLink->getAttribute('href')}";

                    $pLink = Http::get($fullUrl);
                    $contentResourceStore = app(ContentResourceStore::class);

                    $resource = $contentResourceStore->storeResource($pLink, 'application/pdf');
                    $newHref = $contentResourceStore->getLinkForResource($resource);
                    $pdfLink->setAttribute('href', $newHref);
                }
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, preStoreCallable: $preStore);
    }
}
