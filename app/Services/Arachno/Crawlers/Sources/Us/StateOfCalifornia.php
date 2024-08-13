<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * slug: us-state-of-california
 * title: USA, State of California, Bay Area Air Quality Management District (https://www.baaqmd.gov/rules-and-compliance/current-rules)
 * url: https://www.baaqmd.gov/
 */
class StateOfCalifornia extends AbstractCrawlerConfig
{
    private string $baseDomain = 'https://www.baaqmd.gov';

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/api\/admin\/table\/data/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $items = $json['Data'];
        // Loop through the array of item data
        foreach ($items as $item) {
            $href = $item['AdoptedDocument'];
            $titleHref = $item['Name'];
            $date = $item['Amended'];
            preg_match('/(<a href=")(.*)(&amp)/', $href, $matches);
            preg_match('/(>)(.*)(<\/a>)/', $titleHref, $titles);
            if (isset($matches[2]) && str_contains($titleHref, 'rules/')) {
                $href = $matches[2];
                $title = Str::of($titles[2])->after('rules/')->replace('-', ' ');
                $uniqueId = Str::of($href)->after('rev=')->before('&sc_lang=en');
                $workNumber = Str::of($href)->afterLast('/')->before('-pdf')->before('.pdf');
                $pdfLink = $this->baseDomain . $href;
                $catDoc = new CatalogueDoc([
                    'title' => Str::title($title),
                    'start_url' => $pdfLink,
                    'view_url' => $pdfLink,
                    'source_unique_id' => $uniqueId,
                    'language_code' => 'eng',
                    'primary_location_id' => 2338,
                ]);

                $meta = [
                    'work_date' => $date,
                    'work_number' => $workNumber,
                ];

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
                CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                    'doc_meta' => $meta,
                ]);
            }
        }
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
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '2338');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'rule');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catDocMeta->doc_meta['work_number'] ?? '');
        $downloadUrl = $catalogueDoc->start_url;
        $this->arachno->setDocMetaProperty($pageCrawl, 'start_url', $downloadUrl);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $downloadUrl);
        $date = $catDocMeta->doc_meta['work_date'] ?? '';
        if ($date) {
            try {
                /** @var Carbon */
                $workDate = Carbon::createFromFormat('d/m/Y', $date);
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
            } catch (InvalidFormatException $th) {
            }
        }

        return $doc;
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-state-of-california',
            'throttle_requests' => 300,
            'start_urls' => ['type_' . CrawlType::FULL_CATALOGUE->value => [
                new UrlFrontierLink(['url' => 'https://www.baaqmd.gov/api/admin/table/data/98e85da0-0cb4-42bb-8d88-82f68f1a0771/ezlFNkZGNDMyLTY0NzItNEE5MS05NjE0LUNCNUQyMjAwOEVFNX0=/559305009']),
            ]],
        ];
    }
}
