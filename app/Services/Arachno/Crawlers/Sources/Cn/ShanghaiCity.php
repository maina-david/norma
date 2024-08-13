<?php

namespace App\Services\Arachno\Crawlers\Sources\Cn;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;

/**
 * @codeCoverageIgnore
 * slug: cn-shanghai-city
 * title: law.sfj.sh.gov.cn
 * url: https://law.sfj.sh.gov.cn/
 */
class ShanghaiCity extends AbstractCrawlerConfig
{
    /**
     * @return array<string>
     */
    protected function handleStartUrls(): array
    {
        $docTypes = [
            0 => '地方性法规', // Local Regulations
            1 => '市政府规章', // Municipal Regulations
            2 => '行政规范性文件', // Relevant Administrative Normative Documents
        ];
        $indexes = array_keys($docTypes);
        $urls = [];
        foreach ($indexes as $index) {
            $urls[] = new UrlFrontierLink([
                'url' => "https://law.sfj.sh.gov.cn/yidianApi/api/v1/lawsearch?timeliness=&order=0&search_type=2&type={$index}&page=1&page_size=200"]);
        }

        return $urls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'cn-shanghai-city',
            'throttle_requests' => 500,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    ...$this->handleStartUrls(),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/law\.sfj\.sh\.gov\.cn\/yidianApi\/api\/v1\//')) {
                $this->handlePagination($pageCrawl);
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
        }
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $works = $pageCrawl->getJson()['data'] ?? [];
        foreach ($works as $work) {
            $workDate = $work['implement_date'] ?? '';
            $workType = $this->getWorkType($work['law_type']);
            $workType = $workType;
            $uniqueId = $work['law_id'];
            $url = "https://law.sfj.sh.gov.cn/yidianApi/api/v1/lawsearch/{$uniqueId}";
            $viewUrl = "https://law.sfj.sh.gov.cn/#/detail?id={$uniqueId}";
            $title = $work['law_name'];
            $text = $work['laws'][0]['text'];
            $summary = substr($text, 0, 400);
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'summary' => mb_convert_encoding($summary, 'UTF-8'),
                'source_unique_id' => $uniqueId,
                'view_url' => $viewUrl,
                'start_url' => $url,
                'primary_location_id' => 413,
                'language_code' => 'chn',
            ]);

            $meta = [];
            try {
                $meta['work_date'] = $workDate;
                $meta['work_type'] = $workType;
                $meta['issue_number'] = $work['issuing_number'];
            } catch (InvalidFormatException) {
            }

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
        }
    }

    protected function handlePagination(PageCrawl $pageCrawl): void
    {
        $currentPageUrl = $pageCrawl->pageUrl->url;
        $maxPage = (int) $pageCrawl->getJson()['pager']['max_page'];
        preg_match('/&page=(\d+)/', $currentPageUrl, $matches);
        if (!$matches || !$maxPage) {
            return;
        }

        $currentPageNumber = (int) $matches[1];
        if ($currentPageNumber < $maxPage) {
            $nextPageUrl = preg_replace('/&page=(\d+)/', '&page=' . ++$currentPageNumber, $currentPageUrl);
            $l = new UrlFrontierLink([
                'url' => $nextPageUrl]);
            $this->arachno->followLink($pageCrawl, $l);
        }
    }

    protected function getWorkType(string $workType): string
    {
        return match ($workType) {
            '行政规范性文件' => 'law',
            '市政府规章', '地方性法规' => 'regulation',
            default => 'law'
        };
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', $catalogueDoc->language_code);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catalogueDoc->primary_location_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $catalogueDoc->docMeta?->doc_meta['work_type'] ?? null);
        $this->arachno->saveDoc($pageCrawl);

        $pageCrawl->setDoc($doc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCapture(PageCrawl $pageCrawl): void
    {
        $data = $pageCrawl->getJson()['data'] ?? [];
        if (!$data) {
            return;
        }

        $content = $data['richtext_content'];
        $this->arachno->captureContent($pageCrawl, $content);
    }
}
