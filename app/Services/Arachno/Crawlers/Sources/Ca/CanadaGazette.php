<?php

namespace App\Services\Arachno\Crawlers\Sources\Ca;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * slug: ca-canada-gazette
 * title: Canada Gazette
 * url: https://gazette.gc.ca/rp-pr/p1/2023/index-eng.html
 */
class CanadaGazette extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ca-canada-gazette',
            'throttle_requests' => 200,
            'start_urls' => $this->getCustomCodesStartUrls(),
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/gazette\.gc\.ca\/rp-pr\/publications-eng\.html$/')) {
            $this->arachno->followLinksCSS($pageCrawl, 'details ul.colcount-sm-2 li:first-child a[href*="rp-pr"]');
        }
        if ($this->arachno->matchUrl($pageCrawl, '/gazette.gc.ca\/rp-pr\/p(1|2|3)\/\d{4}\/index-eng\.html$/')) {
            $this->arachno->followLinksCSS($pageCrawl, 'a[href*="index-eng.html"], tr td ul.lst-spcd a');
        }
        if ($this->arachno->matchUrl($pageCrawl, '/gazette\.gc\.ca\/rp-pr\/p\d+\/\d{4}\/\d{4}-\d{1,2}-\d{1,2}\/html\/index-eng\.html$|gazette\.gc\.ca\/rp-pr\/p3\/\d{4}\/index-eng\.html$/')) {
            $this->handleIndex($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/gazette\.gc\.ca\/rp-pr\/p(1|2)\/\d{4}\/\d{4}-\d{1,2}-\d{1,2}\/html\/.*\-eng\.html|eng\/AnnualStatutes\/\d{4}.*\/FullText\.html/')
        && !$this->arachno->matchCSS($pageCrawl, '#content .lst-spcd li > a[href*=".html"]')) {
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleIndex(PageCrawl $pageCrawl): void
    {
        $baseUrl = (string) Str::of($pageCrawl->getFinalRedirectedUrl())
            ->beforeLast('/');
        $this->arachno->each($pageCrawl, '#content .lst-spcd li > a[href*=".html"], tr td ul.lst-spcd a', function ($item) use ($baseUrl, $pageCrawl) {
            $docMeta = new DocMetaDto();
            $href = (string) Str::of($item->getAttribute('href'))
                ->after('./');
            $url = $baseUrl . '/' . $href;
            $matchPartThree = $this->arachno->matchUrl($pageCrawl, '/gazette\.gc\.ca\/rp-pr\/p3\/\d{4}\/index-eng\.html$/');
            $uniqueId = $matchPartThree ? Str::before(Str::after($url, 'AnnualStatutes/'), '/') : (string) Str::of($url)
                ->after('/rp-pr');
            $docMeta->source_unique_id = $uniqueId;
            $title = $item->childNodes[0]?->textContent ?? '';
            $docMeta->title = trim($title);
            $url = $matchPartThree ? 'https://laws.justice.gc.ca/PDF/' . $uniqueId . '.pdf' : $url;

            if ($matchPartThree) {
                $docMeta->source_url = $href;
                $docMeta->download_url = $url;
                $docMeta->work_type = 'act';
            }
            $l = new UrlFrontierLink(['url' => $url]);
            $l->_metaDto = $docMeta;

            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        $url = $pageCrawl->getFinalRedirectedUrl();
        $matchPartThree = $this->arachno->matchUrl($pageCrawl, '/https:\/\/laws\.justice\.gc\.ca\/eng\/AnnualStatutes\/.*/');
        $uniqueId = $matchPartThree ? Str::before(Str::after($url, 'https://laws.justice.gc.ca/eng/AnnualStatutes/'), '/') : (string) Str::of($url)
            ->after('/rp-pr');
        $url = Str::afterLast($url, '.') === 'html' ? $url : 'https://laws.justice.gc.ca/PDF/' . $uniqueId . '.pdf';
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $uniqueId);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaPropertyByCSS($pageCrawl, 'title', 'h1.HeadTitle, div#content h1');
        $matches = preg_match('/p\d+/', $url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $matches ? $this->getWorkType('p' . (string) $matches) : 'act');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '388');
        $date = $dom->filter('#content > p:nth-child(2)')->getNode(0)?->textContent ?? '';
        $date = preg_replace('/&nbsp;+/', ' ', $date) ?? null;
        $assentedDate = $dom->filter('div.info p#assentedDate')->getNode(0)?->textContent ?? '';
        $assentedDate = preg_replace('/Assented to\s+/', '', (string) $assentedDate);
        if ($date || $assentedDate) {
            try {
                /** @var Carbon */
                $workDate = $date !== null ? Carbon::createFromFormat('j M Y', (string) trim($date)) : null;
                $date ? $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate->format('Y-m-d')) : $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $assentedDate);
            } catch (InvalidFormatException $th) {
            }
        }
        $this->arachno->saveDoc($pageCrawl);
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
                'query' => '#content, div.docContents',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'head > title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"gazette") and @rel="stylesheet"] | //head//link[contains(@href,"theme-gcwu-fegc/css/theme.css") and @rel="stylesheet"]',
            ],
        ]);
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
        );
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getCustomCodesStartUrls(): array
    {
        $urls = [
            'https://gazette.gc.ca/rp-pr/publications-eng.html',
        ];
        $out = [];
        foreach ($urls as $u) {
            $out[] = new UrlFrontierLink(['url' => $u]);
        }

        return ['type_' . CrawlType::FOR_UPDATES->value => $out];
    }

    /**
     * @param string $workType
     *
     * @return string
     */
    public function getWorkType(string $workType): string
    {
        return match (strtolower($workType)) {
            'p1' => 'notice',
            'p2' => 'regulation',
            default => 'act',
        };
    }
}
