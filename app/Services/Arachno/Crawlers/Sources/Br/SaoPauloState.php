<?php

namespace App\Services\Arachno\Crawlers\Sources\Br;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Str;

/**
 * @codeCoverageIgnore
 * slug: br-sao-paulo-state
 * title: Sao Paulo State
 * url: http://www.corpodebombeiros.sp.gov.br/
 */
class SaoPauloState extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'br-sao-paulo-state',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'http://www.ccb.policiamilitar.sp.gov.br/portalcb/_seguranca-contra-incendio/search-v.php?tipo=ajax&sit=1&cod=14',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'http://www.ccb.policiamilitar.sp.gov.br/portalcb/_seguranca-contra-incendio/search-v.php?tipo=ajax&sit=1&cod=12',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/portalcb\/_seguranca-contra-incendio\/search-v\.php\?tipo=ajax&sit=1&cod/')) {
            $this->updatePageEncoding($pageCrawl);
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function updatePageEncoding(PageCrawl $pageCrawl): void
    {
        // Fix the encoding of the document.
        $encoding = $pageCrawl->domCrawler->getNode(0)?->ownerDocument->encoding ?? 'UTF-8';
        $pageCrawl->domCrawler->clear();
        $pageCrawl->domCrawler->addHtmlContent($pageCrawl->contentCache->response_body ?? '', $encoding);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $bodyContent = $pageCrawl->domCrawler->filter('body')->getNode(0);
        if ($bodyContent !== null) {
            $bodyContent = $bodyContent->textContent;
            $decodedContent = html_entity_decode($bodyContent);
            preg_match_all('/(IT-\d+\/\d{4})\s-\s(.*)/', $decodedContent, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $uniqueId = $match[1];
                $title = $match[2];
                if (str_contains($title, 'Parte')) {
                    preg_match('/(Parte\s)(\d)/', $title, $parts);
                    $uniqueId = Str::of($uniqueId)->replace('IT-15/2019', "IT-15/{$parts[2]}/2019");
                    $idSplit = Str::of($uniqueId)->replace('/', '-')->replace('2019', '19');
                }
                $idSplit = Str::of($uniqueId)->replace('/', '-')->replace('2019', '19');
                $titleRepl = Str::of($title)->replace('/\s+–\s+/', ' ');
                $this->addToCatalogue($pageCrawl, $uniqueId, $title, $idSplit, $titleRepl);
            }
            preg_match_all('/(Decreto\s\d{2}\.\d{3}\/\d{2})\s-\s(.*)/', $decodedContent, $extraMatches, PREG_SET_ORDER);
            foreach ($extraMatches as $extra) {
                $uniqueId = $extra[1];
                $title = $extra[2];
                $idSplit = '';
                if (str_contains($uniqueId, 'Decreto 55.660/10')) {
                    $idSplit = 'dec_est_55660_30MAR2010';
                }
                if (str_contains($uniqueId, 'Decreto 63.911/18')) {
                    $idSplit = 'decreto_63.911';
                }
                if (str_contains($uniqueId, 'Decreto 64.919/20')) {
                    $idSplit = 'Decreto_64.919';
                }
                $titleRepl = Str::of($title)->replace('/\s+–\s+/', ' ');
                $this->addToCatalogue($pageCrawl, $uniqueId, $title, $idSplit, $titleRepl);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param mixed     $uniqueId
     * @param mixed     $title
     * @param mixed     $idSplit
     * @param mixed     $titleRepl
     *
     * @return void
     */
    protected function addToCatalogue(PageCrawl $pageCrawl, $uniqueId, $title, $idSplit, $titleRepl): void
    {
        $catDoc = new CatalogueDoc([
            'title' => $uniqueId . ' - ' . $title,
            'source_unique_id' => $uniqueId . '-' . strtolower(Str::of($titleRepl)->replace(' ', '-')),
            'view_url' => "http://www.ccb.policiamilitar.sp.gov.br/dsci_publicacoes2/_lib/file/doc/{$idSplit}.pdf",
            'start_url' => "http://www.ccb.policiamilitar.sp.gov.br/dsci_publicacoes2/_lib/file/doc/{$idSplit}.pdf",
            'primary_location_id' => '79386',
            'language_code' => 'por',
        ]);

        $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
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
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'por');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '79386');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $downloadUrl = $catalogueDoc->start_url;
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);
        $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'PDF']);
        $this->arachno->followLink($pageCrawl, $l, true);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'legislation');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
