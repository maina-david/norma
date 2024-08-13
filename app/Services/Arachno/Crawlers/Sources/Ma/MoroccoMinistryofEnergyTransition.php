<?php

namespace App\Services\Arachno\Crawlers\Sources\Ma;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Support\DomClosest;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: ma-environnement-go
 * title: Kingdom of Morocco Ministry of Energy Transition and Sustainable Development
 * url: https://www.environnement.gov.ma
 */
class MoroccoMinistryofEnergyTransition extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ma-environnement-gov',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://www.environnement.gov.ma/fr/lois-et-reglementations/textes-juridiques/3469-loi-cadre']),
                    new UrlFrontierLink(['url' => 'https://www.environnement.gov.ma/fr/lois-et-reglementations/textes-juridiques?id=109']),
                    new UrlFrontierLink(['url' => 'https://www.environnement.gov.ma/fr/lois-et-reglementations/textes-juridiques/3468-gouvernance']),
                    new UrlFrontierLink(['url' => 'https://www.environnement.gov.ma/fr/lois-et-reglementations/textes-juridiques?id=940']),
                    new UrlFrontierLink(['url' => 'https://www.environnement.gov.ma/fr/lois-et-reglementations/textes-juridiques?id=112']),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/environnement\.gov\.ma\/fr\/lois-et-reglementations/')
            || $this->arachno->matchUrl($pageCrawl, '/environnement\.gov\.ma\/ar\/cadre-juridique-ar/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\.pdf|\.PDF/')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div[itemprop="articleBody"] tbody tr:not([bgcolor])',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://www.environnement.gov.ma');
                $meta = [];
                /** @var DOMElement $tableRow */
                $tableRow = $crawler->filterXPath('.//tr')->getNode(0);
                if (trim($tableRow->textContent) === '') {
                    return;
                }

                /** @var DOMNode $tableDt */
                $tableDt = $crawler->filter('td a')->getNode(0);
                $closest = app(DomClosest::class);
                /** @var DOMElement $tableData */
                $tableData = $closest->closest($tableDt, 'td')?->getNode(0);
                $title = str_replace('/\n/', '', trim($tableData->textContent));

                $link = $crawler->filter('td a')->link()->getUri();
                if (!str_contains(strtolower($link), '.pdf')) {
                    $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]), true);
                }
                $uniqueId = urldecode($link);
                $uniqueId = Str::of($uniqueId)->after('.ma/')->before('.pdf');

                $languageCode = str_contains(strtolower($title), 'version arabe')
                || str_contains(strtolower($uniqueId), 'ar') ? 'ara' : 'fra';

                try {
                    $date = explode('(', $tableData->nextElementSibling?->textContent ?? '')[1] ?? null;
                    $date = explode(')', $date ?? '')[0] ?? null;

                    $pubDate = $languageCode === 'fra'
                        ? Carbon::parseFromLocale($date ?? '', 'fr')
                        : Carbon::parseFromLocale($date ?? '', 'ar');

                    $meta['effective_date'] = $pubDate;
                } catch (InvalidFormatException) {
                }

                $catDoc = new CatalogueDoc([
                    'title' => $title,
                    'source_unique_id' => $uniqueId,
                    'start_url' => $link,
                    'view_url' => $link,
                    'primary_location_id' => 173834,
                    'language_code' => $languageCode,
                ]);

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
                CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                    'doc_meta' => $meta,
                ]);
            });
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
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 173834);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');

        try {
            $date = $catalogueDoc->docMeta->doc_meta['effective_date'] ?? null;

            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $date);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $date);
        } catch (InvalidFormatException) {
        }
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
