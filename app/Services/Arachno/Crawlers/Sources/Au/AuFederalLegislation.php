<?php

namespace App\Services\Arachno\Crawlers\Sources\Au;

use App\Contracts\Arachno\UrlFrontierLinkInterface;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use DOMElement;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @codeCoverageIgnore
 */
class AuFederalLegislation extends AbstractCrawlerConfig
{
    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
    }

    public function customStartCrawl(Crawl $crawl): void
    {
        $httpClient = HttpClient::create([
            'max_duration' => 120,
        ]);
        $client = new HttpBrowser($httpClient);
        $startUrls = [
            'https://www.legislation.gov.au/Browse/Results/ByTitle/Acts/InForce/A/0/0/all',
        ];
        foreach ($startUrls as $startUrl) {
            $crawler = $client->request('GET', $startUrl);

            $this->extractWorks($crawler, $startUrl, $crawl);

            while (true) {
                $nextButton = $crawler->filterXPath('//*[@id="MainContent_pnlBrowseResults"]//*[@id="ctl00_MainContent_gridBrowse"]//*[@id="ctl00_MainContent_gridBrowse_ctl00"]/thead/tr/td/table/tbody//*[contains(@class,"rgArrPart2")]//input[contains(@class,"rgPageNext") and not(@onclick="return false;")]');
                if ($nextButton->count() === 0) {
                    break;
                }
                $form = $nextButton->form();
                $crawler = $client->submit($form);
                $this->extractWorks($crawler, $startUrl, $crawl);
            }
        }
    }

    private function extractWorks(Crawler $crawler, string $baseUrl, Crawl $crawl): void
    {
        foreach ($crawler->filter('#ctl00_MainContent_gridBrowse_ctl00 > tbody > tr > td:first-of-type a.LegBookmark') as $link) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            // /Html/Text

            $uri = app(LinkToUri::class)->convert($href, $baseUrl);
            // if ((string) $uri !== 'https://www.legislation.gov.au/Details/C2004A05288') {
            //    continue;
            // }
            /** @var UrlFrontierLinkInterface */
            $urlLink = new UrlFrontierLink([
                'crawl_id' => $crawl->id,
                // @phpstan-ignore-next-line
                'anchor_text' => trim(preg_replace('/\s+/', ' ', preg_replace('/\r|\n/', '', $link->textContent))),
                'referer' => $baseUrl,
            ]);
            app(UrlFrontierLinkManager::class)->addToFrontier($uri, $urlLink);
        }
    }
}
