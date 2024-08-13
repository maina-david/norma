<?php

namespace App\Services\Arachno\Crawlers\Sources\Cn;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Stores\Corpus\ContentResourceStore;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Exception;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: cn-china-national
 * title: www.gov.cn
 * url: https://www.gov.cn/zhengce/xxgk/
 */
class ChinaNational extends AbstractCrawlerConfig
{
    /**
     * @return array<string>
     */
    protected function handleStartUrls(): array
    {
        $docTypes = [
            1104 => '国令', // National Laws
            1108 => '国办发', // Issued by the state council
        ];
        $indexes = array_keys($docTypes);
        $urls = [];
        foreach ($indexes as $index) {
            $frontierQuery = "?page=1&docType={$index}";
            $urls[] = new UrlFrontierLink([
                'url' => "https://sousuoht.www.gov.cn/athena/forward/486B5ABFBAD0FF5743F5E82E007EF04DDD6388E7989E9EC9CC7B84917AC81A5F{$frontierQuery}"]);
        }

        return $urls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'cn-china-national',
            'throttle_requests' => 20000,
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
            if ($this->arachno->matchUrl($pageCrawl, '/486B5ABFBAD0FF5743F5E82E007EF04DDD6388E7989E9EC9CC7B84917AC81A5F\?page=/')) {
                preg_match('/page=(\d+)/', $pageCrawl->pageUrl->url, $matches);

                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/(\?page=\d+&docType=\d+)/')
        ) {
            $queryParams = $this->getUrlParams($pageCrawl->pageUrl->url);
            $settings = $this->getPostData(
                (int) $queryParams['docType'],
                (int) $queryParams['page']
            );
            $pageCrawl->setHttpSettings($settings);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getPostData(int $docType, int $page = 1): array
    {
        return [
            'method' => 'POST',
            'options' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Athenaappkey' => 'JsPgNsKtZIHd0BTiVixrqYysXRJMzQuXTCIifRvgKACaxk8ekscCU%2FLqpAJDUdmGm47OpQnW%2FxeyQOI9C%2Fth4PCKWCyzvfWdpDy%2BK79wnOSc8OpQWeWfJB1Bm8QoYgHGuq9It0YvsFtUswgswFLiZTEiqRFNdj5Yl%2Bs4jwU1HFY%3D',
                    'Athenaappname' => '%E5%9B%BD%E7%BD%91%E6%90%9C%E7%B4%A2',
                ],
                'json' => [
                    'childrenInfoIds' => [
                        [
                            "{$docType}",
                        ],
                    ],
                    'pageSize' => 900,
                    'pageNo' => $page,
                    'resultFields' => [
                        'pub_url',
                        'maintitle',
                        'fwzh',
                        'cwrq',
                        'publish_time',
                    ],
                    'sorts' => [
                        [
                            'sortField' => 'publish_time',
                            'sortOrder' => 'DESC',
                        ],
                    ],
                    'code' => '18122f54c5c',
                    'thirdPartyCode' => 'thirdparty_code_107',
                    'thirdPartyTableId' => 30,
                    'trackTotalHits' => 'true',
                    'searchFields' => [
                        [
                            'fieldName' => 'maintitle',
                            'searchWord' => '',
                        ],
                    ],
                    'isPreciseSearch' => 0,
                ],
            ],
        ];
    }

    /**
     * @param string $url
     *
     * @return array<string, mixed>
     */
    protected function getUrlParams(string $url): array
    {
        $urlParts = parse_url($url);
        if (!$urlParts) {
            throw new Exception("Malformed URL - {$url}");
        }
        $queryString = (string) $urlParts['query'];
        parse_str($queryString, $queryStringArray);

        $page = $queryStringArray['page'];
        $docType = $queryStringArray['docType'];
        if (!$page || !$docType) {
            throw new Exception("Malformed URL, the query params 'page' and 'docType' are required - {$url}");
        }

        return [
            'page' => $page,
            'docType' => $docType,
        ];
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $data = $pageCrawl->getJson()['result']['data'] ?? [];
        $works = $data['list'] ?? [];
        foreach ($works as $work) {
            $url = $work['pub_url'];
            $title = $work['maintitle'];

            preg_match('/(content_\d+)\.htm/', $url, $matches);
            if (!$matches) {
                return;
            }
            $uniqueId = $matches[1];
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'view_url' => $url,
                'start_url' => $url,
                'primary_location_id' => '516',
                'language_code' => 'chn',
            ]);

            $meta = [];
            try {
                $meta['work_date'] = $work['cwrq'];
                $meta['effective_date'] = $work['publish_time'];
                $meta['issue_number'] = $work['fwzh'];
            } catch (InvalidFormatException) {
            }

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
            $this->handlePagination($pageCrawl);
        }
    }

    protected function handlePagination(PageCrawl $pageCrawl): void
    {
        $currentPageUrl = $pageCrawl->pageUrl->url;
        $maxPage = (int) $pageCrawl->getJson()['result']['data']['pager']['pageCount'];
        preg_match('/\?page=(\d+)/', $currentPageUrl, $matches);
        if (!$matches || !$maxPage) {
            return;
        }

        $currentPageNumber = (int) $matches[1];
        if ($currentPageNumber < $maxPage) {
            $nextPageUrl = preg_replace('/\?page=(\d+)/', '?page=' . ++$currentPageNumber, $currentPageUrl);
            $l = new UrlFrontierLink([
                'url' => $nextPageUrl]
            );
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
        $meta = $this->extractMetaFromWork($pageCrawl);

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', $catalogueDoc->language_code);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $catalogueDoc->primary_location_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['issue_number'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);

        $this->arachno->setDocMetaProperty($pageCrawl, 'publication_document_number', $meta['noticeNumber'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'publication_number', $meta['gazetteNumber'] ?? null);

        // Save extra meta, might need it later
        $docMeta = $catalogueDoc->docMeta ?? CatalogueDocMeta::create(['catalogue_doc_id' => $catalogueDoc->id, 'doc_meta' => []]);
        $metaArr = $docMeta->doc_meta;
        $metaArr['categories'] = $meta['categories'];
        $metaArr['issuing_authority'] = $meta['issuingAuthority'];
        $docMeta->update(['doc_meta' => $metaArr]);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * Extract metadata from the table just above a work.
     *
     * @param PageCrawl $pageCrawl
     *
     * @return array <string, mixed>
     */
    protected function extractMetaFromWork(PageCrawl $pageCrawl): array
    {
        $meta = [];
        $this->arachno->eachX($pageCrawl, '//div[contains(@class, "pctoubukuang1")]/table//table//tr//td[contains(., "：")]', function (DOMElement $td) use (&$meta) {
            $text = trim($td->textContent);
            if (!str_contains($text, '：')) {
                return;
            }
            $metaName = preg_replace('/：/', '', $text);
            if (!in_array($metaName, ['索 引 号', '发文机关', '主题分类'])) {
                return;
            }

            $metaEl = $td->nextElementSibling;
            if (!$metaEl) {
                return;
            }
            $metaVal = trim($metaEl->textContent);
            switch ($metaName) {
                case '索 引 号':
                    $gazetteNotice = explode('/', $metaVal);
                    $meta['gazetteNumber'] = $gazetteNotice[0];
                    $meta['noticeNumber'] = $gazetteNotice[1];
                    break;
                case '主题分类':
                    $meta['categories'] = explode('、', $metaVal);
                    break;
                case '发文机关':
                    $meta['issuingAuthority'] = $metaVal;
                    break;
            }
        });

        return $meta;
    }

    protected function addToContentResources(DomCrawler $dom, string $path): void
    {
        if ($dom->filter('img[src]')->count() > 0) {
            foreach ($dom->filter('img[src]') as $imgLink) {
                /** @var DOMElement $imgLink */
                $urlParts = explode('/', $imgLink->getAttribute('src'));
                $fileName = end($urlParts);
                $fullUrl = str_contains($imgLink->getAttribute('src'), 'https://www.gov.cn')
                    ? $imgLink->getAttribute('src')
                    : "{$path}/{$fileName}";

                $imLink = retry(4, function () use ($fullUrl) {
                    return Http::timeout(3000)->get($fullUrl);
                }, 3000);

                $contentResourceStore = app(ContentResourceStore::class);

                $resource = $contentResourceStore->storeResource($imLink, 'image/png');
                $newSrc = $contentResourceStore->getLinkForResource($resource);
                $imgLink->setAttribute('src', $newSrc);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCapture(PageCrawl $pageCrawl): void
    {
        /** @var DOMElement $pageContent */
        $pageContent = $pageCrawl->domCrawler->filter('div.container.sj_container > div > div.wrap.mxxgkwrap.mxxgkwrap_gwywj > table > tbody > tr > td > table')->getNode(0);
        $dom = new DomCrawler($pageContent);
        $path = explode('content_', $pageCrawl->pageUrl->url)[0];
        $this->addToContentResources($dom, $path);

        $this->arachno->captureContent($pageCrawl, $dom->outerHtml());
    }
}
