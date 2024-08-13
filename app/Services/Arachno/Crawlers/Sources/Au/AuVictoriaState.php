<?php

namespace App\Services\Arachno\Crawlers\Sources\Au;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 * slug: au-victoria-legislation
 * title: legislation.vic.gov.au
 * url: https://www.legislation.vic.gov.au/
 */
class AuVictoriaState extends AbstractCrawlerConfig
{
    /** @var int */
    protected int $perPage = 40;

    protected string $versionsRegex = '[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'au-victoria-legislation',
            'throttle_requests' => 2000,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => $this->generateFullURL('act_in_force', 1),
                    ]),

                    new UrlFrontierLink([
                        'url' => $this->generateFullURL('sr_in_force', 1),
                    ]),
                ],
                'type_' . CrawlType::WORK_CHANGES_DISCOVERY->value => [
                    new UrlFrontierLink([
                        'url' => $this->generateWhatsNewUrl(1),
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/api\/search\/title_content/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\?law=/')) {
            $this->followToContent($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, "/versions\/{$this->versionsRegex}/")) {
            $this->extractStartUrl($pageCrawl);
        }

        if ($this->arachno->crawlIsWorkChangesDiscovery($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/api\/search\/matchall/')) {
            $this->handleWorkChanges($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.pdf|\.PDF/')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * Generate the full url including the query params.
     *
     * @param string $type The type to add either sr_in_force or act_in_force
     * @param int    $page
     *
     * @return string
     */
    protected function generateFullURL(string $type, int $page): string
    {
        $params = [
            'from' => ($page - 1) * $this->perPage,
            'size' => $this->perPage,
            'includeFilters' => [
                [
                    'term' => [
                        'field_act_sr_status' => 'false',
                    ],
                ],
                [
                    'term' => [
                        'type' => $type,
                    ],
                ],
            ],
            'includeFields' => [
                'title', 'field_in_force_former_title', 'url', 'type', 'legislation_type', 'field_act_sr_number',
                'legislation_year', 'field_act_sr_status_date', 'field_legislation_status', 'field_bill_pre_2004',
                'field_bill_parliament_term',
            ],
            'sort' => [
                'legislation_year' => 'asc',
            ],
            'aggregations' => [
                'legislation_year' => [
                    'terms' => [
                        'field' => 'legislation_year',
                        'size' => 250,
                        'order' => [
                            '_key' => 'desc',
                        ],
                    ],
                ],
            ],
        ];

        $params = http_build_query($params);

        return "https://www.legislation.vic.gov.au/app/api/search/title_content?{$params}";
    }

    protected function generateWhatsNewUrl(int $page): string
    {
        $start = Carbon::now()->toISOString();
        $end = now()->startOfYear()->toISOString();

        $whatsNewParams = [
            'from' => ($page - 1) * $this->perPage,
            'size' => $this->perPage,
            'contentTypes' => [
                'act_as_made', ' sr_as_made', 'act_in_force', 'sr_in_force',
            ],
            'includeFilters' => [
                [
                    'range' => [
                        'field_act_sr_published_date' => [
                            'gt' => $end,
                            'lt' => $start,
                        ],
                    ],
                ],
            ],
            'includeFields' => [
                'title', 'url', 'type', 'legislation_type', 'legislation_year', 'field_in_force_former_title', 'field_act_sr_number', 'field_in_force_version_number',
                'field_in_force_effective_date', 'field_act_sr_published_date',
            ],
            'sort' => [
                [
                    '_score' => 'desc',
                ],
                [
                    'title_az' => 'asc',
                ],
            ],
            'aggregations' => [
                'legislation_type' => [
                    'terms' => [
                        'field' => 'legislation_type',
                        'order' => [
                            '_key' => 'asc',
                        ],
                        'size' => '250',
                    ],
                ],
            ],
        ];

        $whatsNewParams = http_build_query($whatsNewParams);

        return "https://www.legislation.vic.gov.au/app/api/search/matchall?{$whatsNewParams}";
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->addExtraFollowLinks($pageCrawl);
        $json = $pageCrawl->getJson();

        $arr = collect($json['results'] ?? []);

        $arr->each(function ($item) use ($pageCrawl) {
            $title = $item['title'][0];
            $workNumber = $item['field_act_sr_number'][0];
            $year = $item['legislation_year'][0];
            $target = $item['url'][0];
            $url = str_replace('/site-6/', 'https://www.legislation.vic.gov.au/', $target);

            $uniqueId = Str::of($url)->afterLast('/');
            $meta = [];

            $meta['work_number'] = $workNumber . ', ' . $year;

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'view_url' => $url,
                'start_url' => "{$url}?law={$target}",
                'language_code' => 'eng',
                'primary_location_id' => '42323',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
        });
    }

    public function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '42323');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pageCrawl->getFinalRedirectedUrl());
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['work_number'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        // there's no other date to use, so have to use effecctive date
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);

        preg_match('/in-force\/[a-zA-Z-]+/', $catalogueDoc->view_url ?? '', $matches);
        $type = explode('/', $matches[0])[1];
        $workType = $type == 'statutory-rules' ? 'Regulation' : 'Act';

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    protected function followToContent(PageCrawl $pageCrawl): void
    {
        $domCrawler = $pageCrawl->domCrawler;
        /** @var DOMElement $bodyContent */
        $bodyContent = $domCrawler->filter('body')->getNode(0);
        /** @var DOMDocument $owner */
        $owner = $bodyContent->ownerDocument;
        $bodyContent = $owner->saveHTML($bodyContent);
        //        preg_match(sprintf('/drupal_internal__target_id:\d+[^,]+,id:\s*"(%s)"/', $this->versionsRegex), $bodyContent, $matches);
        /** @var string $bodyContent */
        preg_match(sprintf('/version\\\\u002F(%s)/', $this->versionsRegex), $bodyContent, $matches);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "https://www.legislation.vic.gov.au/app/api/acts/act_in_force/versions/{$matches[1]}"]), true);
    }

    protected function extractStartUrl(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();

        $startUrl = $json['authorised_version']['documents'][0]['url'] ?? $json['version']['documents'][1]['url'] ?? $json['version']['documents'][0]['url'];
        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $startUrl]), true);
        $effDate = $json['effective_date'];

        /** @var CatalogueDoc $doc */
        $doc = $pageCrawl->pageUrl->catalogueDoc;
        $doc->load('docMeta');
        $docMeta = $doc->docMeta ?? CatalogueDocMeta::create(['catalogue_doc_id' => $doc->id, 'doc_meta' => []]);
        $meta = $docMeta->doc_meta;
        $meta['effective_date'] = $effDate;
        $docMeta->update(['doc_meta' => $meta]);
    }

    protected function addExtraFollowLinks(PageCrawl $pageCrawl): void
    {
        if (!str_contains($pageCrawl->pageUrl->url, 'from=0&')) {
            return;
        }

        $type = str_contains($pageCrawl->pageUrl->url, 'act_in_force') ? 'act_in_force' : 'sr_in_force';

        $json = $pageCrawl->getJson();
        $totalItems = $json['total'];
        $pages = ceil($totalItems / $this->perPage);

        for ($i = 2; $i <= $pages; $i++) {
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $this->generateFullURL($type, $i)]));
        }
    }

    protected function addWhatsNewFollowLinks(PageCrawl $pageCrawl): void
    {
        if (!str_contains($pageCrawl->pageUrl->url, 'from=0&')) {
            return;
        }

        $json = $pageCrawl->getJson();
        $totalItems = $json['total'];
        $pages = $totalItems > 0 ? ceil($totalItems / $this->perPage) : 0;

        for ($i = 2; $i <= $pages; $i++) {
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $this->generateWhatsNewUrl($i)]), true);
        }
    }

    protected function handleWorkChanges(PageCrawl $pageCrawl): void
    {
        $this->addWhatsNewFollowLinks($pageCrawl);
        /** @var array<string> $uniqueIds */
        $uniqueIds = [];

        $json = $pageCrawl->getJson();
        $arr = collect($json['results'] ?? []);

        $arr->each(function ($item) use (&$uniqueIds) {
            $id = (string) Str::of($item['url'][0])->afterLast('/');
            $uniqueIds[$id] = true;
        });
        $uniqueIds = array_keys($uniqueIds);
        $this->arachno->fetchChangedDocs($uniqueIds, $pageCrawl->pageUrl->crawl?->crawler?->source_id ?? 0);
    }
}
