<?php

namespace App\Services\Arachno\Crawlers\Sources\Br;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;

/**
 * @codeCoverageIgnore
 *
 * slug: br-brazil-federal
 * title: Brazil Federal
 * url: https://www.in.gov.br/leiturajornal
 */
class BrazilFederal extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if (
                $this->arachno->matchUrl($pageCrawl, '/consulta\/-\/buscar\/dou\?q=(?:\+|\*)&s=(?:do1|todos)&exactDate=dia&sortType=0.*(?:.+currentPage=\d+&newPage=\d+.+displayDate=\d+)?$/')
                && $this->arachno->matchCSS($pageCrawl, '.pagination-bar')
            ) {
                $this->handleUpdates($pageCrawl);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/br\/web\/dou\/-\//') && $this->arachno->matchCSS($pageCrawl, 'a[href="javascript:history.go(-1);"], a[id="printFunction"]')) {
            $this->capturePageContent($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $baseUrl = 'https://www.in.gov.br/web/dou/-/';
        $dom = $pageCrawl->domCrawler;
        $lastPage = $dom->filter('button#lastPage')->getNode(0)?->textContent ?? '';
        if (!$lastPage) {
            return;
        }
        $docs = $dom->filter('script[id="_br_com_seatecnologia_in_buscadou_BuscaDouPortlet_params"][type="application/json"]')->getNode(0)?->textContent ?? '';
        $docs = json_decode($docs, true);
        $items = $docs['jsonArray'];
        $lastKey = count($items) - 1;
        foreach ($items as $idx => $docData) {
            $docMeta = new DocMetaDto();
            $urlSlug = $docData['urlTitle'] ?? null;
            if (!$urlSlug) {
                continue;
            }
            $docMeta->title = $docData['title'] ?? null;
            if (!$docMeta->title) {
                return;
            }
            $docMeta->title = html_entity_decode($docMeta->title);
            $docMeta->source_unique_id = 'updates_' . $urlSlug;
            $docMeta->language_code = 'por';
            $docMeta->primary_location = '77838';
            $docMeta->work_type = $this->getWorkType($docData['artType']);
            $docMeta->work_number = $docData['classPK'] ?? null;
            $url = $baseUrl . $urlSlug;
            $docMeta->source_url = $url;
            $docMeta->download_url = $url;
            if ($docData['content']) {
                $docMeta->summary = html_entity_decode($docData['content']);
            }
            if ($docData['pubDate']) {
                try {
                    /** @var Carbon */
                    $workDate = Carbon::createFromFormat('d/m/Y', trim($docData['pubDate']));
                    $docMeta->work_date = $workDate;
                } catch (InvalidFormatException $th) {
                }
            }
            if ($docData['hierarchyList']) {
                $docMeta['hierarchyList'] = $docData['hierarchyList'];
            }

            $l = new UrlFrontierLink(['url' => $url, 'anchor_text' => $docMeta->title ?? 'DocumentContent']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);

            if ($idx == $lastKey) {
                /** @var DOMElement|null */
                $nextPageArrow = $dom->filter('button#rightArrow')->getNode(0) ?? null;
                if (!$nextPageArrow) {
                    return;
                }
                /** @var DOMElement */
                $arrowWrapper = $nextPageArrow->parentNode;
                $lastPageWrapper = $arrowWrapper->previousElementSibling;
                if (!$lastPageWrapper) {
                    return;
                }
                $lastPage = $lastPageWrapper->textContent;
                $lastPage = trim($lastPage) == '' ? 1 : (int) $lastPage;

                $parts = parse_url($pageCrawl->getFinalRedirectedUrl());
                if (!isset($parts['query'])) {
                    return;
                }
                parse_str($parts['query'], $query);
                $currentPage = $query['currentPage'] ?? 1;
                $newPage = $query['newPage'] ?? 1;
                /** @var string */
                $id = $docData['classPK'] ?? '';
                /** @var string */
                $displayDate = $docData['displayDateSortable'] ?? '';

                if ($newPage == $lastPage) {
                    return;
                }

                $currentPage = (int) $newPage;
                $newPage = (int) $newPage + 1;
                $nextPage = "https://www.in.gov.br/consulta/-/buscar/dou?q=*&s=todos&exactDate=dia&sortType=0&delta=20&currentPage={$currentPage}&newPage={$newPage}&score=0&id={$id}&displayDate={$displayDate}";

                $l = new UrlFrontierLink(['url' => $nextPage, 'anchor_text' => 'Next Page']);
                $this->arachno->followLink($pageCrawl, $l, true);
            }
        }
    }

    /**
     * @param string $workType
     *
     * @return string
     */
    public function getWorkType(string $workType): string
    {
        return match (strtolower($workType)) {
            'Portaria' => 'ordinance',
            'Despacho' => 'order',
            'Ato Declaratório' => 'declaration',
            'Alvará' => 'licence',
            'Resolução' => 'resolution',
            'Norma Complementar' => 'standard',
            'Retificação' => 'rectification',
            'Ato' => 'act',
            default => 'act'
        };
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
                'type' => DomQueryType::CSS,
                'query' => 'article[id="materia"]',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'div[class="informacao-conteudo-dou"]',
            ],
        ]);
        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries);
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'br-brazil-federal',
            'throttle_requests' => 200,
            'start_urls' => $this->getCustomCodesStartUrls(),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getCustomCodesStartUrls(): array
    {
        return ['type_' . CrawlType::FOR_UPDATES->value => [
            new UrlFrontierLink(['url' => 'https://www.in.gov.br/consulta/-/buscar/dou?q=+&s=do1&exactDate=dia&sortType=0']),
        ]];
    }
}
