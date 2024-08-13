<?php

namespace App\Services\Arachno\Crawlers\Sources\Dk;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

/**
 * @codeCoverageIgnore
 *
 * slug: dk-denmark-lovtidende
 * title: Denmark Lovtidende
 * url: https://www.lovtidende.dk
 */
class DenmarkLovtidende extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'dk-denmark-lovtidende',
            'throttle_requests' => 500,
            'start_urls' => $this->getDenmarkStartUrls(),
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/lovtidende\.dk\/api\/proclamation\/latest/')) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/\/api\/pdf\/.*/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        foreach ($json as $documents) {
            // Access the documents property to get the array of documents
            $items = $documents['documents'];

            // Loop through the array of documents
            foreach ($items as $item) {
                $docMeta = new DocMetaDto();
                // extract the metadata from each document
                $docMeta->source_unique_id = $item['id'];
                $docMeta->work_number = $item['publicationNumber'];
                $docMeta->publication_number = $item['accessionsNummer'];
                $docMeta->language_code = 'dan';
                $docMeta->title = $item['title'];
                $docMeta->work_type = 'notice';
                $docMeta->primary_location = '111809';
                $docMeta->source_url = $item['retsinfoLink'];
                $downloadUrl = 'https://www.lovtidende.dk/api/pdf/' . $item['id'];
                $docMeta->download_url = $downloadUrl;
                $workType = $item['shortName'] ?? 'notice';
                $workType = explode(' ', $workType)[0] ?? 'notice';
                $workTypes = $this->getWorkTypes();
                $docMeta->work_type = $workTypes[$workType] ?? 'notice';
                $date = $item['offentliggoerelsesDato'];
                if ($date) {
                    try {
                        /** @var Carbon */
                        $workDate = Carbon::createFromFormat('d/m/Y', trim($date));
                        $workDate = $workDate->format('Y-m-d');
                        $docMeta->work_date = Carbon::parse($workDate);
                    } catch (InvalidFormatException $th) {
                    }
                }
                $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'PDF']);
                $l->_metaDto = $docMeta;
                $this->arachno->followLink($pageCrawl, $l, true);
            }
        }
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getDenmarkStartUrls(): array
    {
        return ['type_' . CrawlType::FOR_UPDATES->value => [
            new UrlFrontierLink([
                'url' => 'https://www.lovtidende.dk/api/proclamation/latest/10',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.lovtidende.dk/api/proclamation/latest/20',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.lovtidende.dk/api/proclamation/latest/30',
            ]),
        ]];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, string>
     */
    protected function getWorkTypes(): array
    {
        return [
            'LOVE' => 'act',
            'LOV' => 'act',
            'BEK' => 'order',
            'LTB' => 'act',
            'BKI' => 'notice',
            'LBK' => 'notice',
        ];
    }
}
