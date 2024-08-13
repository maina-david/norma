<?php

namespace App\Services\Arachno\Crawlers\Sources\ERM;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\TocItemDraft;

/**
 * @codeCoverageIgnore
 * slug: erm-themis
 * title: ERM Themis
 * url: https://themis.erm.com
 */
class Themis extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'erm-themis',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://themis.erm.com/api',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleCapture($pageCrawl);
        }
    }

    public function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $docs = [
            [
                // for BP
                'title' => 'NWCAA Air Operating Permit',
                'source_unique_id' => '3fe2ff64-d75c-4545-a82b-018ad6d1f703',
                'primary_location_id' => 172882,
                'language_code' => 'eng',
                'start_url' => 'https://themis.erm.com/api/ruleContent/getSections?ruleId=3fe2ff64-d75c-4545-a82b-018ad6d1f703&apikey=c8ad3325-bfe3-4cfe-ba90-56b676e6d976',
                'view_url' => 'https://themis.erm.com/bp/regview/Rule/3fe2ff64-d75c-4545-a82b-018ad6d1f703',
                'work_type' => 'permit',
            ],
            [
                // for BP
                'title' => 'National Pollutant Discharge Elimination System (NPDES) Issuance for BP Cherry Point Refinery',
                'source_unique_id' => '0c99264e-9ceb-44c6-bf62-0186f20d014a',
                'primary_location_id' => 172882,
                'language_code' => 'eng',
                'start_url' => 'https://themis.erm.com/api/ruleContent/getSections?ruleId=0c99264e-9ceb-44c6-bf62-0186f20d014a&apikey=c8ad3325-bfe3-4cfe-ba90-56b676e6d976',
                'view_url' => 'https://themis.erm.com/bp/regview/Rule/0c99264e-9ceb-44c6-bf62-0186f20d014a',
                'work_type' => 'permit',
            ],
            [
                // for BP
                'title' => 'State of Washington Dangerous Waste Management Permit for Corrective Action',
                'source_unique_id' => '1c7fc193-1982-4fb3-a2f3-0186f03efc71',
                'primary_location_id' => 172882,
                'language_code' => 'eng',
                'start_url' => 'https://themis.erm.com/api/ruleContent/getSections?ruleId=1c7fc193-1982-4fb3-a2f3-0186f03efc71&apikey=c8ad3325-bfe3-4cfe-ba90-56b676e6d976',
                'view_url' => 'https://themis.erm.com/bp/regview/Rule/1c7fc193-1982-4fb3-a2f3-0186f03efc71',
                'work_type' => 'permit',
            ],
        ];

        foreach ($docs as $doc) {
            $catDoc = new CatalogueDoc($doc);
            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            $catalogueDoc->docMeta?->update(['doc_meta' => ['work_type' => $doc['work_type']]]);
        }
    }

    public function handleCapture(PageCrawl $pageCrawl): void
    {
        $data = $pageCrawl->getJson();

        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '172882');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $data['rule']['identifier']);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $catalogueDoc->docMeta->doc_meta['work_type'] ?? 'permit');
        $this->arachno->saveDoc($pageCrawl);

        $html = '';
        foreach ($data['rule']['sections'] as $section) {
            $label = str_replace($data['rule']['identifier'], '', $section['identifier']);
            /** @var string */
            $label = preg_replace('/^\W+/', '', $label);
            $label = $label . ' ' . ($section['title'] ?? '');

            $html .= PHP_EOL . '<a id="ID' . $section['id'] . '"></a>' . PHP_EOL;
            $html .= PHP_EOL . '<h3 class="heading">' . $label . '</h3>' . PHP_EOL;
            $html .= str_replace('\r\n', PHP_EOL, $section['contents']);

            $this->arachno->createTocItem($pageCrawl, new TocItemDraft([
                'source_unique_id' => $section['id'],
                'href' => $pageCrawl->pageUrl->url . '#ID' . $section['id'],
                'label' => $label,
                'level' => 1,
                'position' => $section['sortOrder'],
            ]));
        }
        $this->arachno->captureContent($pageCrawl, $html);
    }
}
