<?php

namespace App\Services\Arachno\Crawlers\Sources\Vn;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: vn-congbao
 * title: Vietnam Congbao
 * url: https://congbao.chinhphu.vn/
 */
class VietnamCongbao extends AbstractCrawlerConfig
{
    private string $baseDomain = 'https://congbao.chinhphu.vn';

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/home$/')) {
                $this->followLinks($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/cong\-bao\-so/')) {
                $this->handleMetaForUpdates($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/noi\-dung\-van\-ban\-so/')) {
                $this->followPdfLink($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/cong\-bao\-nam\-\d{4}/')) {
                $this->followLinks($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/cong\-bao\-so/')) {
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/noi\-dung\-van\-ban\-so/')) {
                $this->handleMeta($pageCrawl);
                $this->followPdfLink($pageCrawl);
            }
        }
        if ($this->arachno->matchUrl($pageCrawl, '/pdf/')) {
            $pageCrawl->setOcrSettings(['provider' => 'pdfocr', 'languages' => ['vie']]);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/cong\-bao\-nam\-\d{4}/')) {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => [
                        'render_js' => true,
                        'wait_browser' => 'networkidle0',
                        'wait_for' => 'a[href*="/cong-bao"]',
                    ],
                ]);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/cong\-bao\-so/') || $this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/home$/')) {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => [
                        'render_js' => true,
                        'wait_browser' => 'networkidle0',
                        'wait_for' => 'div.title-vb',
                    ],
                ]);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) || $this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/congbao\.chinhphu\.vn\/noi\-dung\-van\-ban\-so/')) {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => [
                        'render_js' => true,
                        'wait_browser' => 'networkidle0',
                        'wait_for' => 'ul.dropdown-menu-right li a[href*="pdf"]',
                    ],
                ]);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/pdf/')) {
            $response = retry(4, function () use ($pageCrawl) {
                return Http::timeout(5000)->withHeaders(['User-Agent' => $pageCrawl->getSettingUserAgent()])->get($pageCrawl->pageUrl->url);
            }, 5000); // Adds timeout, every page returns cookie page on first fetch

            preg_match('/cookie="([^"]+)/', $response->body(), $matches);

            if (isset($matches[1])) {
                [$name, $value] = explode('=', $matches[1]);
                $pageCrawl->setHttpSettings([
                    'options' => [
                        'cookies' => CookieJar::fromArray([$name => $value], 'congbao.chinhphu.vn'),
                    ],
                ]);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followPdfLink(PageCrawl $pageCrawl): void
    {
        $downloadUrl = $pageCrawl->domCrawler->filter('ul.dropdown-menu-right li a[href*="pdf"]');
        if ($downloadUrl->count() > 1) {
            $this->mergePDFs($pageCrawl);

            return;
        }

        $pdfLink = $downloadUrl->link()->getUri();
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pdfLink);
        $docMeta = new DocMetaDto();
        $l = new UrlFrontierLink(['url' => $pdfLink, 'anchor_text' => 'PDF']);
        $l->_metaDto = $docMeta;
        $this->arachno->followLink($pageCrawl, $l);
    }

    protected function mergePDFs(PageCrawl $pageCrawl): void
    {
        $pageCrawl->pageUrl->update(['doc_id' => $pageCrawl->getDoc()?->id]);

        $pdfLink = $pageCrawl->domCrawler->filter('ul.dropdown-menu-right li a[href*="pdf"]');
        $count = $pdfLink->count();

        $pdfLinks = [];
        $plainDir = sprintf('tmp/%d%s', now()->getTimestampMs(), Str::random(8));
        $workingDir = storage_path("app/{$plainDir}");
        $mergedPdf = "{$workingDir}/merged.pdf";

        Storage::disk('local')->createDirectory($plainDir);

        for ($i = 0; $i < $count; $i++) {
            $content = $this->fetchPDF($pageCrawl, $pdfLink->eq($i)->link()->getUri());
            //            $response = Http::get($pdfLink->eq($i)->link()->getUri());
            $filename = "{$workingDir}/file_{$i}.pdf";
            $pdfLinks[] = $filename;
            file_put_contents($filename, $content);
        }

        // Increase the timeout to 600 seconds
        $output = Process::timeout(600)
            ->run([
                'gs', '-q', '-dNOPAUSE', '-dBATCH', '-sDEVICE=pdfwrite',
                "-sOutputFile={$mergedPdf}",
                ...$pdfLinks,
            ]);

        if ($output->successful()) {
            /** @var string $generated */
            $generated = file_get_contents($mergedPdf);
            $pageCrawl->domCrawler->clear();
            $pageCrawl->domCrawler->addContent($generated, 'application/pdf');
            /** @var ContentCache $contentCache */
            $contentCache = $pageCrawl->contentCache;
            $contentCache->response_body = $generated;

            $this->arachno->capturePDF($pageCrawl);
        }

        Storage::disk('local')->deleteDirectory($plainDir);
    }

    protected function fetchPDF(PageCrawl $pageCrawl, string $url): string
    {
        $response = retry(4, function () use ($pageCrawl, $url) {
            return Http::timeout(5000)->withHeaders(['User-Agent' => $pageCrawl->getSettingUserAgent()])->get($url);
        }, 5000);
        preg_match('/cookie="([^"]+)/', $response->body(), $matches);

        if (!isset($matches[1])) {
            return $response->body();
        }

        [$name, $value] = explode('=', $matches[1]);

        $response = retry(4, function () use ($pageCrawl, $name, $value, $url) {
            return Http::timeout(5000)->withHeaders(['User-Agent' => $pageCrawl->getSettingUserAgent()])
                ->withCookies([$name => $value], 'congbao.chinhphu.vn')
                ->get($url);
        }, 5000);

        return $response->body();
    }

    /**
     * @return array<string>
     */
    protected function getyearUrls(): array
    {
        $endYear = now()->startOfYear()->year;
        $fullCrawl = [];

        for ($startYear = 2010; $startYear <= $endYear; $startYear++) {
            $fullCrawl[] = new UrlFrontierLink([
                'url' => "{$this->baseDomain}/cong-bao-nam-{$startYear}",
            ]);
        }

        return $fullCrawl;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followLinks(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        $links = $dom->filter('div.list-group a[href*="/cong-bao"]');
        foreach ($links as $link) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $fullLink = "{$this->baseDomain}{$href}";
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $fullLink]));
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMetaForUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.title-vb > a[href]', function ($link) use ($pageCrawl) {
            $href = $link?->getAttribute('href');
            if ($href === '#') {
                return;
            }
            $fullLink = $this->baseDomain . $href;
            $uniqueId = Str::of($href)->after('/');
            $title = $link->textContent ?? '';
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = 'updates_' . $uniqueId;
            $docMeta->title = $title;
            $docMeta->language_code = 'vie';
            $docMeta->primary_location = '42473';
            $docMeta->work_type = 'gazette';
            $docMeta->source_url = $fullLink;
            $l = new UrlFrontierLink(['url' => $fullLink, 'anchor_text' => 'title']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.title-vb > a[href]', function ($link) use ($pageCrawl) {
            $dom = new DomCrawler($link->parentNode->parentNode);
            $hrefLink = $dom->filter('div.title-vb > a[href]')->getNode(0);
            /** @var DOMElement $hrefLink */
            $href = $hrefLink->getAttribute('href');
            $fullHref = "{$this->baseDomain}{$href}";
            $uniqueId = (string) Str::of($href)->after('/');
            $title = $hrefLink->textContent ?? '';
            $workDate = $dom->filter('div.col-md-2.text-center')->getNode(0);
            $workDate = $workDate->textContent ?? null;
            $meta = [];
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $fullHref,
                'view_url' => $fullHref,
                'source_unique_id' => $uniqueId,
                'language_code' => 'vie',
                'primary_location_id' => 42473,
            ]);

            try {
                /** @var Carbon */
                $workDate = Carbon::parse($workDate);
                $meta['work_date'] = $workDate;
            } catch (InvalidFormatException) {
            }

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'vie');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '42473');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'gazette');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'start_url', $catalogueDoc->start_url);
        $workDate = $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null;
        if ($workDate) {
            try {
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
            } catch (InvalidFormatException $th) {
            }
        }

        $this->arachno->saveDoc($pageCrawl);

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
            'slug' => 'vn-congbao',
            'throttle_requests' => 500,
            'verify_ssl' => false,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => "{$this->baseDomain}/home",
                    ]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    ...$this->getYearUrls(),
                ],
            ],
        ];
    }
}
