<?php

namespace App\Services\Arachno\Crawlers\Sources\Us\Ca;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Throwable;

/**
 * @codeCoverageIgnore
 *  slug: us-sacramento-airquality
 *  title: Sacramento County Air Quality
 *  url: https://www.airquality.org/
 */
class SacramentoMetropolitan extends AbstractCrawlerConfig
{
    /**
     * @throws Throwable
     * @throws ConnectionException
     *
     * @return array<UrlFrontierLink>
     */
    protected function getCrawlUrls(): array
    {
        $urls = [];
        //        $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'])
        //            ->get('https://www.airquality.org/Businesses/Rules-Regulations');
        $response = retry(4, function () {
            return Http::timeout(3000)->withHeaders(['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'])
                ->get('https://www.airquality.org/Businesses/Rules-Regulations');
        }, 2000);
        $htmlContent = $response->body();
        preg_match_all('/"queryGroupName":"([^"]+)"/', $htmlContent, $matches);
        $groupNames = collect($matches[1])->unique()->values();

        foreach ($groupNames as $groupName) {
            $urls[] = new UrlFrontierLink(['url' => "https://www.airquality.org/ProgramCoordination/_vti_bin/client.svc/ProcessQuery?groupName={$groupName}"]);
        }

        return $urls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-sacramento-airquality',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    //                    ...$this->getCrawlUrls(),
                    new UrlFrontierLink(['url' => 'https://www.airquality.org/Businesses/Rules-Regulations']),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/ProgramCoordination\/_vti_bin\/client\.svc\/ProcessQuery/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\?merge-/')) {
                $this->mergePdfs($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/') && !$this->arachno->matchUrl($pageCrawl, '/\?merge-/')) {
                $this->handleMeta($pageCrawl);
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    /**
     * @throws ConnectionException
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $links = $this->getCrawlUrls();

            foreach ($links as $link) {
                $this->arachno->followLink($pageCrawl, $link, true);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/ProgramCoordination\/_vti_bin\/client\.svc\/ProcessQuery/')) {
            $this->handleSettings($pageCrawl);
        }
    }

    protected function handleSettings(PageCrawl $pageCrawl): void
    {
        $groupName = Str::of($pageCrawl->pageUrl->url)->after('groupName=');

        $xml = view('arachno.us.ca.documents', ['groupName' => $groupName->value()])->render();

        $settings = [
            'method' => 'POST',
            'options' => [
                'headers' => [
                    'Content-Type' => 'text/xml; charset=UTF8',
                    'X-Requestdigest' => '0x62FB4584B0E19313D9C88BE7CEC257A39700200E8C4CA57DB28DB712B846060C4774684218C7CF07EB7AEB0BCEC8DADBF14333EC09FB79D97E0EE5CFC6CDEADF,26 Jun 2024 07:39:47 -0000',
                ],
                'body' => $xml,
            ],
        ];

        $pageCrawl->setHttpSettings($settings);
    }

    /**
     * @param PageCrawl            $pageCrawl
     * @param array<string, mixed> $meta
     *
     * @return void
     */
    protected function addToCatalogue(PageCrawl $pageCrawl, array $meta): void
    {
        $catDoc = new CatalogueDoc([
            'title' => $meta['title'],
            'source_unique_id' => $meta['source_unique_id'],
            'start_url' => $meta['start_url'],
            'view_url' => $meta['view_url'],
            'primary_location_id' => 2618,
            'language_code' => 'eng',
        ]);

        $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
    }

    /**
     * @param PageCrawl            $pageCrawl
     * @param array<string, mixed> $json
     *
     * @return array<string>
     */
    protected function fetchLinks(PageCrawl $pageCrawl, array $json): array
    {
        $links = [];
        $groupName = Str::of($pageCrawl->pageUrl->url)->after('?groupName=');
        $items = array_values(array_filter($json, fn ($item) => isset($item['e110ba64-e2d4-4b52-858d-e2e70ee2257cDefault'])))[0] ?? [];

        foreach ($items as $item) {
            $resultTables = $item['ResultTables'];

            foreach ($resultTables as $resultTable) {
                $resultRows = $resultTable['ResultRows'];

                foreach ($resultRows as $resultRow) {
                    $title = $resultRow['owstaxIdscRegulation'];

                    $title = explode('|', $title)[3];
                    $title = Str::of($title)->before(';');

                    $link = $resultRow['Path'];

                    $uniqueId = explode(' ', strtolower($title));
                    $uniqueId = implode('-', $uniqueId);
                    $links[] = "{$link}?title={$title}?id={$uniqueId}?groupName={$groupName}";
                }
            }
        }

        return $links;
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $links = $this->fetchLinks($pageCrawl, $json);

        $link = Str::of($links[0])->before('?');
        $meta = [
            'title' => Str::of($links[0])->after('title=')->before('?'),
            'source_unique_id' => Str::of($links[0])->after('?id=')->before('?'),
            'start_url' => $link,
            'view_url' => $link,
        ];

        if (count($links) > 1) {
            $arg = Str::of($meta['start_url'])->afterLast('/')->before('.pdf');
            $groupName = Str::of($links[0])->after('?groupName=');
            $meta['start_url'] = "{$link}?merge-{$arg}?groupName={$groupName}";
            $this->addToCatalogue($pageCrawl, $meta);
        }

        $this->addToCatalogue($pageCrawl, $meta);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @throws ConnectionException
     * @throws FilesystemException
     *
     * @return void
     */
    protected function mergePdfs(PageCrawl $pageCrawl): void
    {
        //        $options = [
        //            'provider' => 'scraping_bee',
        //            'options' => [
        //                'render_js' => true,
        //                'wait_for' => 'table.ms-rteTable-4.footable.responsiveTable.footable-loaded',
        //            ],
        //        ];
        //
        //        $pageCrawl->setProxySettings($options);
        //        /** @var DomCrawler $crawler */
        //        $crawler = $this->arachno->fetchLink($pageCrawl, 'https://www.airquality.org/Businesses/Rules-Regulations', ['proxy_settings' => $options]);

        $groupName = Str::of($pageCrawl->pageUrl->url)->after('groupName=');

        $xml = view('arachno.us.ca.documents', ['groupName' => $groupName->value()])->render();

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=UTF8',
            'X-Requestdigest' => '0x62FB4584B0E19313D9C88BE7CEC257A39700200E8C4CA57DB28DB712B846060C4774684218C7CF07EB7AEB0BCEC8DADBF14333EC09FB79D97E0EE5CFC6CDEADF,26 Jun 2024 07:39:47 -0000',
        ])->withBody($xml)->post('https://www.airquality.org/ProgramCoordination/_vti_bin/client.svc/ProcessQuery');

        $json = $response->json();
        $links = $this->fetchLinks($pageCrawl, $json);

        $count = count($links);

        $pdfLinks = [];
        $plainDir = sprintf('tmp/%d%s', now()->getTimestampMs(), Str::random(8));
        $workingDir = storage_path("app/{$plainDir}");
        $mergedPdf = "{$workingDir}/merged.pdf";

        Storage::disk('local')->createDirectory($plainDir);

        for ($i = 0; $i < $count; $i++) {
            $link = explode('?', $links[$i])[0];
            $response = Http::get($link);
            $filename = "{$workingDir}/file_{$i}.pdf";
            $pdfLinks[] = $filename;
            file_put_contents($filename, $response->body());
        }

        $data = compact('mergedPdf', 'plainDir', 'pdfLinks');

        $this->storeAndRemoveFile($pageCrawl, $data);
    }

    /**
     * @param PageCrawl            $pageCrawl
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function storeAndRemoveFile(PageCrawl $pageCrawl, array $data): void
    {
        $process = Process::timeout(200)
            ->run([
                'gs', '-q', '-dNOPAUSE', '-dBATCH', '-sDEVICE=pdfwrite',
                "-sOutputFile={$data['mergedPdf']}",
                ...$data['pdfLinks'],
            ]);

        if ($process->successful()) {
            /** @var string $generated */
            $generated = file_get_contents($data['mergedPdf']);
            $pageCrawl->domCrawler->clear();
            $pageCrawl->domCrawler->addContent($generated, 'application/pdf');
            /** @var ContentCache $contentCache */
            $contentCache = $pageCrawl->contentCache;
            $contentCache->response_body = $generated;

            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }

        Storage::disk('local')->deleteDirectory($data['plainDir']);
    }

    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 2618);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'regulation');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
