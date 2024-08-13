<?php

namespace App\Services\Arachno\Crawlers\Sources\Jp;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: jp-reiki-metro-tokyo
 * title: Tokyo Metro
 * url: https://www.reiki.metro.tokyo.lg.jp/
 */
class ReikiMetroTokyo extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'jp-reiki-metro-tokyo',
            'throttle_requests' => 500,
            'start_urls' => $this->getTokyoStartUrls(),
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && ($this->arachno->matchUrl($pageCrawl, '/www\.reiki\.metro\.tokyo\.lg\.jp\/reiki\/reiki_honbun\/[0-9A-Za-z]+\.html$/') || $this->arachno->matchUrl($pageCrawl, '/www\.city\.minato\.tokyo\.jp\/reiki\/reiki_honbun\/[0-9A-Za-z]+\.html$/'))) {
            $this->handleCapture($pageCrawl);
            $this->handleToc($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $criteriaFunc = function ($nodeDTO) {
            $n = $nodeDTO['node'];
            $ulParent = $n->parentNode?->parentNode?->parentNode;
            if ($ulParent && strtolower($ulParent->nodeName) === 'ul' && str_contains($ulParent->getAttribute('class'), 'none')) {
                return false;
            }
            $href = $nodeDTO['node']->getAttribute('href');

            return strtolower($n->nodeName) === 'a' && $href && $href !== '#';
        };
        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']?->getAttribute('href');

            return $href;
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            '#joubun-toc > div > ul > li > ul',
            $criteriaFunc,
            null,
            null,
            $extractUniqueId,
            null,
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div#primaryInner table#scrollable01 tbody tr td a', function ($entry) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($entry->parentNode->parentNode);
            /** @var DOMElement */
            $pdfLink = $entryCrawler->filter('td:nth-child(1) a')->getNode(0);
            $title = $entryCrawler->filter('td:nth-child(1)')->getNode(0)?->textContent ?? '';
            $workDate = $entryCrawler->filter('td:nth-child(2)')->getNode(0)?->textContent ?? '';
            $workType = $entryCrawler->filter('td:nth-child(3)')->getNode(0)?->textContent ?? '';
            $hrefVal = (string) Str::of($pdfLink->getAttribute('href'))->replace('..', '');
            $hreflink = $this->arachno->matchUrl($pageCrawl, '/www\.reiki\.metro\.tokyo\.lg\.jp\/reiki/') ? 'https://www.reiki.metro.tokyo.lg.jp/reiki' : 'https://www.city.minato.tokyo.jp/reiki';
            $url = $hreflink . $hrefVal;
            $uniqueId = (string) Str::of($hrefVal)->afterLast('/')->replace('.html', '');
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $url,
                'view_url' => $url,
                'source_unique_id' => $uniqueId,
                'language_code' => 'jpn',
                'primary_location_id' => $this->arachno->matchUrl($pageCrawl, '/www\.reiki\.metro\.tokyo\.lg\.jp\/reiki/') ? '153756' : '153785',
            ]);
            $meta = [
                'work_date' => trim($workDate),
                'work_type' => trim($workType),
            ];
            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        /** @var CatalogueDocMeta */
        $catDocMeta = CatalogueDocMeta::find($catalogueDoc->id);
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'jpn');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catalogueDoc->primary_location_id);
        $workTypeString = $catDocMeta->doc_meta['work_type'] ?? '';
        $typeText = $this->extractWorkTypeFromText($workTypeString);
        $workType = $this->getTokyoWorkType(trim($typeText));
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $date = $catDocMeta->doc_meta['work_date'] ?? '';
        if ($date) {
            try {
                $dateString = Str::of($date)->replace('◆', '');
                // Match the era, year, month, and day using RegEX
                $pattern = '/(昭和|平成|令和)(\d+)年(\d+)月(\d+)日/';
                // ◆令和2年3月12日
                preg_match($pattern, $dateString, $matches);

                $era = $matches[1];
                $year = intval($matches[2]);
                $month = intval($matches[3]);
                $day = intval($matches[4]);

                // Convert the era year to the Gregorian calendar year
                if ($era === '昭和') {
                    $year += 1925;
                } elseif ($era === '平成') {
                    $year += 1988;
                } elseif ($era === '令和') {
                    $year += 2018;
                }

                // Use Carbon to Extract values
                $date = Carbon::createFromDate($year, $month, $day);
                $formattedDate = $date->format('Y-m-d');
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $formattedDate);
            } catch (InvalidFormatException $th) {
            }
        }

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
                'query' => 'div#primaryInner2',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'head > title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"reiki.css") and @rel="stylesheet"] | //head//link[contains(@href,"gstatic.com") and @rel="stylesheet"]',
            ],
        ]);
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
        );
    }

    private function extractWorkTypeFromText(string $workTypeString): string
    {
        $typesArray = [
            '/勅令/',
            '/政令/',
            '/法律/',
            '/府省令/',
            '/憲法/',
            '/規則/',
            '/規制/',
            '/知らせ/',
        ];
        $workType = '';
        foreach ($typesArray as $typeTxt) {
            if (preg_match($typeTxt, $workTypeString)) {
                $workType = preg_replace($typeTxt, '', $workTypeString) ?? '';
                $workType = str_replace($workType, '', $workTypeString);
                $workType = $workType;
            }
        }

        return $workType;
    }

    private function getTokyoWorkType(string $type): string
    {
        return match ($type) {
            '勅令', '政令' => 'decree',
            '法律' => 'law',
            '府省令' => 'ordinance',
            '規則' => 'rule',
            '憲法' => 'constitution',
            '規制' => 'regulation',
            '知らせ' => 'notice',

            default => 'law',
        };
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getTokyoStartUrls(): array
    {
        return ['type_' . CrawlType::FULL_CATALOGUE->value => [
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_a.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_i.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_u.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_e.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_o.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ka.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ki.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ku.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ke.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ko.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_sa.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_si.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_su.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_se.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_so.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ta.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ti.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_tu.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_te.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_to.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_na.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ni.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_no.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ha.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_hi.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_hu.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_he.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ho.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ma.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_mi.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_mu.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_me.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_mo.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ya.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_yu.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_yo.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ri.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_re.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.reiki.metro.tokyo.lg.jp/reiki/reiki_kana/r_50_ro.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_01.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_02.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_03.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_04.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_05.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_06.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_07.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_08.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_09.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_10.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_11.html',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.city.minato.tokyo.jp/reiki/reiki_taikei/r_taikei_12.html',
            ]),
        ]];
    }
}
