<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Actions\Arachno\Doc\HandleCrawlComplete;
use App\Models\Arachno\CasemakerChange;
use App\Models\Arachno\Crawl;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Stores\Corpus\CatalogueDocStore;
use DOMElement;
use DOMNode;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 */
class CaseMakerUSCFROld extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'US-ADMIN';

    public function customStartCrawl(Crawl $crawl): void
    {
        if ($crawl->isTypeFullCatalogue()) {
            $this->handleFullCatalogueUSCRF($crawl);
        }
        if ($crawl->isTypeWorkChangesDiscovery()) {
            $this->findUSCFRChanges($crawl);
        }
    }

    public function handleFullCatalogueUSCRF(Crawl $crawl): void
    {
        $titles = [
            ['id' => 'title0001', 'number' => 'Title 01', 'heading' => 'General Provisions'],
            ['id' => 'title0002', 'number' => 'Title 02', 'heading' => 'Grants and Agreements'],
            ['id' => 'title0003', 'number' => 'Title 03', 'heading' => 'The President'],
            ['id' => 'title0004', 'number' => 'Title 04', 'heading' => 'Accounts'],
            ['id' => 'title0005', 'number' => 'Title 05', 'heading' => 'Administrative Personnel'],
            ['id' => 'title0006', 'number' => 'Title 06', 'heading' => 'Domestic Security'],
            ['id' => 'title0007', 'number' => 'Title 07', 'heading' => 'Agriculture'],
            ['id' => 'title0008', 'number' => 'Title 08', 'heading' => 'Aliens and Nationality'],
            ['id' => 'title0009', 'number' => 'Title 09', 'heading' => 'Animals and Animal Products'],
            ['id' => 'title0010', 'number' => 'Title 10', 'heading' => 'Energy'],
            ['id' => 'title0011', 'number' => 'Title 11', 'heading' => 'Federal Elections'],
            ['id' => 'title0012', 'number' => 'Title 12', 'heading' => 'Banks and Banking'],
            ['id' => 'title0013', 'number' => 'Title 13', 'heading' => 'Business Credit and Assistance'],
            ['id' => 'title0014', 'number' => 'Title 14', 'heading' => 'Aeronautics and Space'],
            ['id' => 'title0015', 'number' => 'Title 15', 'heading' => 'Commerce and Foreign Trade'],
            ['id' => 'title0016', 'number' => 'Title 16', 'heading' => 'Commercial Practices'],
            ['id' => 'title0017', 'number' => 'Title 17', 'heading' => 'Commodity and Securities Exchanges'],
            ['id' => 'title0018', 'number' => 'Title 18', 'heading' => 'Conservation of Power and Water Resources'],
            ['id' => 'title0019', 'number' => 'Title 19', 'heading' => 'Customs Duties'],
            ['id' => 'title0020', 'number' => 'Title 20', 'heading' => 'Employees\' Benefits'],
            ['id' => 'title0021', 'number' => 'Title 21', 'heading' => 'Food and Drugs'],
            ['id' => 'title0022', 'number' => 'Title 22', 'heading' => 'Foreign Relations'],
            ['id' => 'title0023', 'number' => 'Title 23', 'heading' => 'Highways'],
            ['id' => 'title0024', 'number' => 'Title 24', 'heading' => 'Housing and Urban Development'],
            ['id' => 'title0025', 'number' => 'Title 25', 'heading' => 'Indians'],
            ['id' => 'title0026', 'number' => 'Title 26', 'heading' => 'Internal Revenue'],
            ['id' => 'title0027', 'number' => 'Title 27', 'heading' => 'Alcohol, Tobacco Products and Firearms'],
            ['id' => 'title0028', 'number' => 'Title 28', 'heading' => 'Judicial Administration'],
            ['id' => 'title0029', 'number' => 'Title 29', 'heading' => 'Labor'],
            ['id' => 'title0030', 'number' => 'Title 30', 'heading' => 'Mineral Resources'],
            ['id' => 'title0031', 'number' => 'Title 31', 'heading' => 'Money and Finance: Treasury'],
            ['id' => 'title0032', 'number' => 'Title 32', 'heading' => 'National Defense'],
            ['id' => 'title0033', 'number' => 'Title 33', 'heading' => 'Navigation and Navigable Waters'],
            ['id' => 'title0034', 'number' => 'Title 34', 'heading' => 'Education'],
            ['id' => 'title0036', 'number' => 'Title 36', 'heading' => 'Parks, Forests, and Public Property'],
            ['id' => 'title0037', 'number' => 'Title 37', 'heading' => 'Patents, Trademarks, and Copyrights'],
            ['id' => 'title0038', 'number' => 'Title 38', 'heading' => 'Pensions, Bonuses, and Veterans\' Relief'],
            ['id' => 'title0039', 'number' => 'Title 39', 'heading' => 'Postal Service'],
            ['id' => 'title0040', 'number' => 'Title 40', 'heading' => 'Protection of Environment'],
            ['id' => 'title0041', 'number' => 'Title 41', 'heading' => 'Public Contracts and Property Management'],
            ['id' => 'title0042', 'number' => 'Title 42', 'heading' => 'Public Health'],
            ['id' => 'title0043', 'number' => 'Title 43', 'heading' => 'Public Lands: Interior'],
            ['id' => 'title0044', 'number' => 'Title 44', 'heading' => 'Emergency Management and Assistance'],
            ['id' => 'title0045', 'number' => 'Title 45', 'heading' => 'Public Welfare'],
            ['id' => 'title0046', 'number' => 'Title 46', 'heading' => 'Shipping'],
            ['id' => 'title0047', 'number' => 'Title 47', 'heading' => 'Telecommunication'],
            ['id' => 'title0048', 'number' => 'Title 48', 'heading' => 'Federal Acquisition Regulations System'],
            ['id' => 'title0049', 'number' => 'Title 49', 'heading' => 'Transportation'],
            ['id' => 'title0050', 'number' => 'Title 50', 'heading' => 'Wildlife and Fisheries'],
        ];
        foreach ($titles as $title) {
            $sourceUniqueId = 'us-admin-' . $title['id'];
            $catDoc = new CatalogueDoc([
                'title' => 'Code of Federal Regulations - ' . $title['number'] . ' - ' . $title['heading'] . ' (' . $sourceUniqueId . ')',
                'start_url' => (string) new Uri(static::URL_PREFIX . $sourceUniqueId),
                'view_url' => null,
                'source_unique_id' => $sourceUniqueId,
                'language_code' => 'eng',
            ]);

            /** @var \App\Models\Arachno\Crawler $crawler */
            $crawler = $crawl->crawler;
            app(CatalogueDocStore::class)->create($catDoc, $crawler);
        }
    }

    /**
     * When CrawlUrl is implemented by a config, it is called instead of the generic crawl.
     *
     * @param PageCrawl $pageCrawl
     *
     * @return DomCrawler
     */
    public function crawlUrl(PageCrawl $pageCrawl): DomCrawler
    {
        $file = str_replace(static::URL_PREFIX, '', urldecode($pageCrawl->pageUrl->url));
        $year = $this->getMaxYear('USCFR/US');
        // e.g. us-2023-admin-title0001
        $fileTitlePrefix = (string) preg_replace('/^([a-z]{2})\-/', '$1-' . $year . '-', $file);
        $fullDoc = null;
        $titleNode = null;
        foreach ($this->getFilesListing('USCFR/US/' . $year . '/ADMIN/XML/') as $filename) {
            $name = Str::afterLast($filename, '/');
            if (!Str::of($name)->startsWith($fileTitlePrefix)) {
                continue;
            }

            // important to keep trying to fetch the file, otherwise we'll miss text in the doc
            $xml = $this->crawlUrlAttemptFileFetch($filename, 10);
            if (!$xml) {
                throw new Exception('Failed to crawl frontier ID: ' . $pageCrawl->pageUrl->id);
            }
            $xml = $this->replaceHtmlEntities($xml);

            if (is_null($fullDoc)) {
                $fullDoc = new DomCrawler($xml);
                /** @var DOMElement */
                $titleNode = $fullDoc->filterXPath('//code[@type="Root"]/code[@type="Title"]')->getNode(0);
                continue;
            }
            if (!$titleNode) {
                throw new Exception('Failed to crawl frontier ID: ' . $pageCrawl->pageUrl->id);
            }
            $xmlDoc = new DomCrawler($xml);

            // if ($filename === 'USCFR/US/2023/ADMIN/XML/us-2023-admin-title0001-part0006.xml') {
            // break;
            // }
            $this->mergeSectionsIntoFullDoc($pageCrawl, $fullDoc, $xmlDoc);
        }

        if (!$fullDoc) {
            /** @var CatalogueDoc $doc */
            $doc = $pageCrawl->pageUrl->catalogueDoc;
            app(HandleCrawlComplete::class)->stopFetchStarted($doc);

            return $pageCrawl->domCrawler;
        }

        $xml = $fullDoc->outerHtml();
        $this->crawlUrlProcessXML($pageCrawl, $file, $xml);

        return $pageCrawl->domCrawler;
    }

    /**
     * @param PageCrawl  $pageCrawl
     * @param DomCrawler $fullDoc
     * @param DomCrawler $xmlDoc
     *
     * @return void
     */
    protected function mergeSectionsIntoFullDoc(PageCrawl $pageCrawl, DomCrawler $fullDoc, DomCrawler $xmlDoc): void
    {
        /** @var DOMElement|null */
        $firstSection = $xmlDoc->filterXPath('//code[@type="Section"]')->getNode(0);
        if (!$firstSection) {
            return;
        }

        /** @var DOMElement */
        $parent = $firstSection->parentNode;
        $xpath = $this->generateXpathToNode($parent);
        /** @var DOMElement|null */
        $parentInFullDoc = $fullDoc->filterXPath($xpath)->getNode(0);
        if ($parentInFullDoc) {
            foreach ($xmlDoc->filterXPath('//code[@type="Part"]') as $part) {
                /** @var DOMNode */
                $node = $parentInFullDoc->ownerDocument?->importNode($part, true);
                $parentInFullDoc->appendChild($node);
            }
        } else {
            $found = false;
            /** @var DOMElement */
            $parentNode = $firstSection;
            while (!$found && $parentNode->getAttribute('type') !== 'Title') {
                /** @var DOMElement */
                $parentNode = $parentNode->parentNode;
                $xpath = $this->generateXpathToNode($parentNode);
                $nodeInFullDoc = $fullDoc->filterXPath($xpath)->getNode(0);
                if ($nodeInFullDoc) {
                    $found = true;
                    foreach ($parentNode->childNodes as $codeNode) {
                        if (strtolower($codeNode->nodeName) !== 'code') {
                            continue;
                        }
                        /** @var DOMNode */
                        $node = $nodeInFullDoc->ownerDocument?->importNode($codeNode, true);
                        $nodeInFullDoc->appendChild($node);
                    }
                }
            }
        }
    }

    /**
     * @param DOMElement $node
     *
     * @return string
     */
    protected function generateXpathToNode(DOMElement $node): string
    {
        /** @var DOMElement */
        $parentNode = $node;
        $xpath = '';
        while ($parentNode->getAttribute('type') !== 'Title') {
            $name = (new DomCrawler($parentNode))->filterXPath('//name[1]')->getNode(0)?->textContent ?? '';
            if (str_contains($name, '"')) {
                // if the name tag contains quotes, then we need to change the xpath query (tried various ways of escaping that didn't work)
                $str = '';
                foreach (explode('"', $name) as $chunk) {
                    $str = $str . ' and child::name[contains(text(), "' . $chunk . '")]';
                }
                $xpath = '/code[@type="' . $parentNode->getAttribute('type') . '" ' . $str . ']' . $xpath;
            } else {
                $xpath = '/code[@type="' . $parentNode->getAttribute('type') . '" and child::name[text()="' . $name . '"]]' . $xpath;
            }

            /** @var DOMElement */
            $parentNode = $parentNode->parentNode;
        }

        $xpath = '//code[@type="Title"]' . $xpath;

        return $xpath;
    }

    /**
     * @param Crawl $crawl
     *
     * @return void
     */
    public function findUSCFRChanges(Crawl $crawl): void
    {
        foreach ($this->getFilesListing('USCFR/1Dated') as $filename) {
            if (!Str::of($filename)->endsWith('Finished.txt')) {
                continue;
            }
            // e.g. "USCFR/1Dated/us_admin_2022_20220801-1-1-Publication-Finished.txt"
            $changeVersion = str_replace(['USCFR/1Dated/', '-1-1-Publication-Finished.txt'], '', $filename);
            // should leave us with us_admin_2022_20220801

            if (CasemakerChange::where('title', $changeVersion)->exists()) {
                continue;
            }

            // $name = e.g. us_admin_2022_20220801
            [$jurisdiction, $type, $year] = explode('_', $changeVersion);
            $folder = $this->getFileFolderFromNameParts($jurisdiction, $type, (int) $year, true);
            if (!$this->checkIfDirectoryExists($folder)) {
                continue;
            }
            $uniqueIds = [];
            $folder = 'USCFR/US/' . $year . '/ADMIN/XML/PublishedChanges/Changes/';
            foreach ($this->getFilesListing($folder) as $changedFileName) {
                // e.g. USCFR/US/2023/ADMIN/XML/PublishedChanges/Changes/us-2023-admin-title0050-part0218.xml
                $title = (string) Str::of($changedFileName)
                    ->after('PublishedChanges/Changes/')
                    ->after('-admin-')
                    ->before('-');
                $uniqueIds['us-admin-' . $title] = true;
            }
            $uniqueIds = array_keys($uniqueIds);

            $this->arachno->fetchChangedDocs($uniqueIds, $crawl->crawler->source_id ?? 144);

            CasemakerChange::create(['title' => $changeVersion]);
        }
    }
}
