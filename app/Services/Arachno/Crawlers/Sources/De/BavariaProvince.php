<?php

namespace App\Services\Arachno\Crawlers\Sources\De;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: de-gesetze-bayern
 * title: gesetze-bayern.de
 * url: https://www.gesetze-bayern.de/.
 */
class BavariaProvince extends AbstractCrawlerConfig
{
    protected string $baseLink = 'https://www.gesetze-bayern.de';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'de-gesetze-bayern',
            'throttle_requests' => 100,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.gesetze-bayern.de/Search/Filter/DOKTYP/norm',
                    ]),
                ],
                'type_' . CrawlType::SEARCH->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.gesetze-bayern.de/Search/Filter/DOKTYP/norm',
                    ]),
                ],
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/Search\/Filter\/DOKTYP/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => ['render_js' => false],
            ]);
        }
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/Search\/Filter\/DOKTYP\/norm/')) {
            $response = Http::get('https://www.gesetze-bayern.de/Search/Filter/DOKTYP/norm');

            $this->arachno->setCrawlCookies($pageCrawl, $response->cookies);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/Search\/Filter\/DOKTYP\/norm/')
            || $this->arachno->matchUrl($pageCrawl, '/\/Search\/Page\//')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsSearch($pageCrawl)) {
            $this->handleSearch($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/Content\/Document/')) {
            $this->handleMeta($pageCrawl);

            if ($this->arachno->matchCSS($pageCrawl, '.tree')) {
                $this->handleTocLinks($pageCrawl);
            }
            $this->handleCapture($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleSearch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/Search\/Filter\/DOKTYP\/norm/')) {
            $this->followNextLink($pageCrawl);

            $this->arachno->each($pageCrawl, '.hitlistItem', function (DOMElement $link) use ($pageCrawl) {
                $domCrawler = new DomCrawler($link);
                /** @var DOMElement $link */
                $link = $domCrawler->filter('a')->getNode(0);
                $link = "{$this->baseLink}{$link->getAttribute('href')}";
                $link = Str::of($link)->before('?hl');

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]));
            });
        }
        if ($this->arachno->matchUrl($pageCrawl, '/Content\/Document/')) {
            $url = $pageCrawl->pageUrl->url;
            $docId = Str::of($url)->after('Document/');

            $links = $this->generateHrefs($docId, $docId);

            foreach ($links as $link) {
                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]), true);
            }
        } else {
            $this->arachno->indexForSearch(
                $pageCrawl,
                contentQuery: new DomQuery(['query' => '.cont', 'type' => DomQueryType::CSS]),
                primaryLocationId: 108495,
                languageCode: 'deu',
            );
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.hitlistItem', function (DOMElement $link) use ($pageCrawl) {
            $domCrawler = new DomCrawler($link);
            $meta = [];

            /** @var DOMElement $title */
            $title = $domCrawler->filter('.hltitel')->getNode(0);
            $title = $title->textContent;
            $title = str_replace('/\n/', '', $title);
            /** @var DOMElement $url */
            $url = $domCrawler->filter('a')->getNode(0);
            $url = "{$this->baseLink}{$url->getAttribute('href')}";
            $url = Str::of($url)->before('?hl');

            /** @var DOMElement $effDate */
            $effDate = $domCrawler->filter('.hlSubTitel')->getNode(0);
            $effDate = trim(Str::of($effDate->textContent)->after(':'));
            $effDate = Carbon::parse($effDate);

            $uniqueId = Str::of($url)->afterLast('/')->before('?');

            $meta['effective_date'] = $effDate;
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'source_unique_id' => $uniqueId,
                'view_url' => $url,
                'start_url' => $url,
                'primary_location_id' => '108495',
                'language_code' => 'deu',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;

            if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
                $this->followNextLink($pageCrawl);
            }
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followNextLink(PageCrawl $pageCrawl): void
    {
        /** @var DOMElement|null $nextLink */
        $nextLink = $pageCrawl->domCrawler->filter('#pager li:last-child a')->getNode(0);
        if ($nextLink) {
            $href = $nextLink->getAttribute('href');
            $href = "{$this->baseLink}{$href}";
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $href, 'anchor_text' => 'Next']));
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    public function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');

        $uniqueId = $catalogueDoc->source_unique_id;
        $downloadUrl = "https://www.gesetze-bayern.de/Content/Pdf/{$uniqueId}?all=True";

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'deu');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '108495');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'regulation');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCapture(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.cont',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@rel,"stylesheet")][not(contains(@href,"/Home/SessionCss"))]',
            ],
        ]);

        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
        );
    }

    /**
     * @param string $docId
     * @param string $fragmentId
     *
     * @return array<string>
     */
    protected function generateHrefs(string $docId, string $fragmentId): array
    {
        $tocUrl = "https://www.gesetze-bayern.de/Content/TocSubTree/{$docId}/{$fragmentId}";

        $response = Http::get($tocUrl);
        $tree = collect($response->json());

        $allHrefs = [];

        foreach ($tree as $node) {
            $href = "{$this->baseLink}/Content/Document/{$node['id']}";
            $allHrefs[] = $href;

            $generated = $this->generateHrefs($docId, $node['id']);
            $allHrefs = [...$allHrefs, ...$generated];
        }

        return $allHrefs;
    }

    protected function handleTocLinks(PageCrawl $pageCrawl): void
    {
        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']?->getAttribute('href');

            return Str::of($href)->after('Document/');
        };

        $this->arachno->captureTocCss(
            $pageCrawl,
            '.tree',
            null,
            null,
            null,
            $extractUniqueId,
        );
    }
}
