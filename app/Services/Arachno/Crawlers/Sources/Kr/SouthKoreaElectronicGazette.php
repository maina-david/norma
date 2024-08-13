<?php

namespace App\Services\Arachno\Crawlers\Sources\Kr;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 * slug: kr-gwanbo-go
 * title: Electronic Official Gazette of the Republic of Korea
 * url: https://gwanbo.go.kr/
 */
class SouthKoreaElectronicGazette extends AbstractCrawlerConfig
{
    /**
     * @return array<int, \App\Models\Arachno\UrlFrontierLink>
     */
    protected function getCategoryNum(): array
    {
        $urls = [];
        $categories = [1, 2, 4, 5, 6, 7, 10, 11, 18, 22];
        foreach ($categories as $category) {
            $urls[] = new UrlFrontierLink(['url' => "https://gwanbo.go.kr/SearchRestApi.jsp?cat={$category}"]);
        }

        return $urls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'kr-gwanbo-go',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    ...$this->getCategoryNum(),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/SearchRestApi\.jsp/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/common\/ofcttCntntDownload\.do/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/SearchRestApi\.jsp/')) {
            $url = $pageCrawl->pageUrl->url;
            /** @var int $category */
            $category = explode('cat=', $url)[1];
            $this->getDailyPostData($pageCrawl, $category);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/common\/ofcttCntntDownload\.do/')) {
            $url = $pageCrawl->pageUrl->url;
            $tocId = explode('tocId=', $url)[1];
            $this->getContentPostData($pageCrawl, $tocId);
        }
    }

    protected function getDailyPostData(PageCrawl $pageCrawl, int $category): void
    {
        $date = Carbon::now()->format('Ymd');
        $postData = [
            'method' => 'POST',
            'options' => [
                'form_params' => [
                    'mode' => 'daily',
                    'index' => 'gwanbo',
                    'query' => "keyword_field_regdate:[{$date} TO {$date}] AND unstored_field_keyword:(관보 AND 정호) AND keyword_category_order:({$category})",
                    'pQuery_tmp' => '',
                    'pageNo' => 1,
                    'listSize' => 300,
                    'sort' => '',
                ],
                'headers' => [
                    'accept' => 'application/json',
                ],
            ],
        ];

        $settings = $postData;

        $pageCrawl->setHttpSettings($settings);
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $tocId
     *
     * @return void
     */
    protected function getContentPostData(PageCrawl $pageCrawl, string $tocId): void
    {
        $contentPostData = [
            'method' => 'POST',
            'options' => [
                'form_params' => [
                    'cntnt_seq_no' => $tocId,
                ],
                'headers' => [
                    'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                ],
            ],
        ];

        $settings = $contentPostData;

        $pageCrawl->setHttpSettings($settings);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $data = $json['data'][0]['list'] ?? [];

        $docMetaDto = new DocMetaDto();
        foreach ($data as $item) {
            $title = $item['keyword_category_name'];
            $url = $item['stored_field_url'];
            $tocId = Str::of($url)->after('tocId=')->before('&isTocOrder');
            $uniqueId = $item['stored_file_name'];
            $workType = $item['stored_category_name'];
            $workDate = $item['keyword_field_regdate'];

            $docMetaDto->title = $title;
            $docMetaDto->source_unique_id = $uniqueId;
            $docMetaDto->primary_location = '155958';
            $docMetaDto->language_code = 'kor';
            $docMetaDto->work_type = $this->getWorkType($workType);
            $docMetaDto->source_url = 'https://gwanbo.go.kr' . $url;
            $docMetaDto->download_url = 'https://gwanbo.go.kr' . $url;

            try {
                $workDate = Carbon::parse($workDate);
                $docMetaDto->work_date = $workDate;
            } catch (InvalidFormatException) {
            }

            $link = new UrlFrontierLink([
                'url' => "https://gwanbo.go.kr/user/common/ofcttCntntDownload.do?tocId={$tocId}",
            ]);
            $link->_metaDto = $docMetaDto;
            $this->arachno->followLink($pageCrawl, $link, true);
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getWorkType(string $type): string
    {
        return match ($type) {
            '헌법' => 'constitution',
            '대통령령, 총리령, 부령' => 'decree',
            '훈령' => 'order',
            '고시' => 'notification',
            '공고' => 'notice',
            '대통령지시사항' => 'directive',
            default => 'law'
        };
    }
}
