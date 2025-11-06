<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Actions\Arachno\Doc\HandleCrawlComplete;
use App\Models\Arachno\CasemakerChange;
use App\Models\Arachno\Crawl;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Geonames\Location;
use App\Models\Ontology\WorkType;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Stores\Corpus\CatalogueDocStore;
use App\Stores\Corpus\ContentResourceStore;
use Aws\S3\S3Client;
use DOMDocument;
use DOMElement;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use League\Flysystem\Ftp\FtpAdapter;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Throwable;
use XSLTProcessor;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractCaseMakerConfig extends AbstractCrawlerConfig
{
    use UsesCaseMakerFtp;

    protected ?S3Client $s3Client = null;

    protected string $jurisdictionCode = '';

    public const URL_PREFIX = 'https://internal.norma.com/casemaker/';

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
        if ($crawl->isTypeFullCatalogue()) {
            $this->crawlAdminAndStatForCatalogue($crawl, $this->jurisdictionCode);
        }
        if ($crawl->isTypeWorkChangesDiscovery()) {
            $this->findChanges($crawl);
        }
    }

    // /**
    //  * @param int    $crawlId
    //  * @param string $name
    //  *
    //  * @return void
    //  */
    // protected function addFileToFrontier(int $crawlId, string $name): void
    // {
    //     $uri = new Uri(static::URL_PREFIX . $name);
    //     /** @var UrlFrontierLinkInterface */
    //     $urlLink = new UrlFrontierLink([
    //         'url' => (string) $uri,
    //         'crawl_id' => $crawlId,
    //     ]);
    //     app(UrlFrontierLinkManager::class)->addToFrontier($uri, $urlLink);
    // }

    /**
     * @param Crawl  $crawl
     * @param string $name
     *
     * @return CatalogueDoc
     */
    protected function createCatalogueDocFromFile(Crawl $crawl, string $name): CatalogueDoc
    {
        $sourceUniqueId = $this->filenameToUniqueId($name);

        $juriType = (string) Str::of((string) $sourceUniqueId)->match('/^[a-z]{2}\-[a-z]+\-/')->beforeLast('-');
        $name = $this->getJurisdictionConfig()[$juriType]['name'] ?? '';

        $catDoc = new CatalogueDoc([
            'title' => $name . ' (' . $sourceUniqueId . ')',
            'start_url' => (string) new Uri(static::URL_PREFIX . $sourceUniqueId),
            'view_url' => null,
            'source_unique_id' => $sourceUniqueId,
            'language_code' => 'eng',
        ]);

        /** @var \App\Models\Arachno\Crawler $crawler */
        $crawler = $crawl->crawler;

        return app(CatalogueDocStore::class)->create($catDoc, $crawler);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    protected function filenameToUniqueId(string $fileName): string
    {
        return (string) preg_replace('/^([a-z]{2})\-[0-9]{4}/', '$1', str_replace('.xml', '', $fileName));
    }

    /**
     * @param string $jurisdiction
     * @param string $type
     * @param int    $year
     * @param bool   $changes
     *
     * @return string
     */
    protected function getFileFolderFromNameParts(string $jurisdiction, string $type, int $year, bool $changes = false): string
    {
        $changesSuffix = $changes ? '/PublishedChanges/Changes' : '';
        if (strtolower($jurisdiction) === 'us' && strtolower($type) === 'stat') {
            return 'US/' . $year . '/STAT/XML' . $changesSuffix;
        }
        if (strtolower($jurisdiction) === 'us' && strtolower($type) === 'admin') {
            return 'USCFR/US/' . $year . '/ADMIN/XML' . $changesSuffix;
        }

        return strtoupper($jurisdiction) . '/' . $year . '/' . strtoupper($type) . '/XML' . $changesSuffix;
    }

    /* --------------------------------------------------------------------------------------------------------- */
    /* --------------------------------------------------------------------------------------------------------- */
    /* --------------------------------------------------------------------------------------------------------- */

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
        $juri = (string) Str::of($file)->before('-')->upper();
        $year = $this->getMaxYear($juri);
        $file = ((string) preg_replace('/^([a-z]{2})\-/', '$1-' . $year . '-', $file)) . '.xml';

        [$jurisdiction, $year, $type] = explode('-', $file);
        $filePath = $this->getFileFolderFromNameParts($jurisdiction, $type, (int) $year);
        $xml = $this->crawlUrlAttemptFileFetch($filePath . '/' . $file);
        if ($xml === null) {
            $previousYear = (int) $year - 1;
            $file = str_replace($year, (string) $previousYear, $file);
            $filePath = str_replace($year, (string) $previousYear, $filePath);
            $xml = $this->crawlUrlAttemptFileFetch($filePath . '/' . $file);
        }

        $this->crawlUrlProcessXML($pageCrawl, $file, $xml);

        return $pageCrawl->domCrawler;
    }

    /**
     * @param string $filePath
     * @param int    $maxAttempts
     *
     * @return string|null
     */
    protected function crawlUrlAttemptFileFetch(string $filePath, int $maxAttempts = 3): ?string
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->getDisk();
        /** @var FtpAdapter $adapter */
        $adapter = $disk->getAdapter();
        $attempts = 0;
        $xml = null;
        // it seems that sometimes the connection drops, so let's retry a few times
        while ($attempts < $maxAttempts && !$xml) {
            $attempts++;
            $adapter->__destruct();
            try {
                $xml = $disk->get($filePath);
            } catch (Throwable $th) {
            }

            if (!$xml) {
                sleep(2);
            }
        }

        return $xml;
    }

    /**
     * @param PageCrawl   $pageCrawl
     * @param string      $fileName
     * @param string|null $xml
     *
     * @return void
     */
    protected function crawlUrlProcessXML(PageCrawl $pageCrawl, string $fileName, ?string $xml): void
    {
        if (!$xml) {
            /** @var CatalogueDoc $catalogue */
            $catalogue = $pageCrawl->pageUrl->catalogueDoc;
            app(HandleCrawlComplete::class)->stopFetchStarted($catalogue);

            return;
        }

        $xml = $this->replaceHtmlEntities($xml);

        $pageCrawl->domCrawler->addContent($xml);

        $parts = Str::of($fileName)
            ->replace('.xml', '')
            ->explode('-');
        $jurisdiction = (string) $parts[0];
        $sourceUniqueId = $this->filenameToUniqueId($fileName);
        $doc = $this->createDocFromXml($pageCrawl, $sourceUniqueId, $jurisdiction);
        $this->removeOldVersions($pageCrawl->domCrawler);
        $this->createTocItemsFromXml($pageCrawl->domCrawler, $doc);

        HandleCrawlComplete::dispatch($doc->id)
            ->onQueue('arachno-checks');
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $sourceUniqueId
     * @param string    $jurisdiction
     *
     * @return Doc
     */
    protected function createDocFromXml(PageCrawl $pageCrawl, string $sourceUniqueId, string $jurisdiction): Doc
    {
        $pageUrl = $pageCrawl->pageUrl;
        $domCrawler = $pageCrawl->domCrawler;

        $doc = new Doc([
            'crawl_id' => $pageUrl->crawl_id,
            'source_unique_id' => $sourceUniqueId,
            'title' => $this->getTitle($domCrawler),
            'source_id' => $pageUrl->crawl?->crawler?->source_id,
            'crawler_id' => $pageUrl->crawl?->crawler?->id,
            'primary_location_id' => $this->getLocationId($jurisdiction),
            'delete_if_unused' => false,
            'for_update' => false,
        ]);
        if ($pageUrl->crawl?->crawler?->source) {
            $doc->source()->associate($pageUrl->crawl->crawler->source);
        }

        $doc->setUid();
        $doc->save();

        /** @var WorkType|null $workType */
        $workType = WorkType::where('slug', 'code')->first();
        $doc->docMeta->update([
            'language_code' => 'eng',
            'work_type_id' => $workType?->id,
        ]);

        return $doc;
    }

    protected function getTitle(DomCrawler $domCrawler): string
    {
        // $parts = explode('-', $fileName);
        // $jurisdiction = $parts[0];
        // $type = $parts[2];
        // // if USCFR
        // $partial = $parts[4] ?? '';

        $rootNode = $domCrawler->filterXPath('//code[@type="Root"]');
        $docName = $rootNode->filterXPath('//name')->getNode(0)?->textContent ?? '';
        $firstCode = $rootNode->filterXPath('//code[@type="Root"]/code')->first();
        /** @var DOMElement */
        $firstLevelNode = $firstCode->getNode(0);

        $firstType = $firstLevelNode->getAttribute('type');
        $firstNumber = $domCrawler->filterXPath('//code[@type="Root"]/code/number')->first()->getNode(0)->textContent ?? '';
        $firstName = $domCrawler->filterXPath('//code[@type="Root"]/code/name')->first()->getNode(0)->textContent ?? '';
        $titleParts = [];
        if ($docName) {
            $titleParts[] = $docName;
        }
        if ($firstType && !in_array($firstType, ['Undesignated', 'Unprefixed', 'Topic'])) {
            $firstType = $firstType;
        } else {
            $firstType = '';
        }
        if ($firstType === 'CodeTitle') {
            $firstType = 'Title';
        }

        if ($firstNumber || $firstType) {
            $titleParts[] = trim($firstType . ' ' . $firstNumber);
        }
        if ($firstName) {
            $titleParts[] = $firstName;
        }
        // if USCFR
        // if (strtolower($jurisdiction) === 'us' && strtolower($type) === 'admin') {
        //     // e.g. part0023_0049.xml or part0304.xml
        //     $partialParts = explode('_', str_replace(['part', '.xml'], '', $partial));
        //     $partialParts = array_map(fn ($p) => ltrim($p, '0'), $partialParts);
        //     $titleParts[] = 'Part ' . implode('-', $partialParts);
        //     // should change to "Part 23-49" or "Part 304" respectively
        // }

        return implode(' - ', $titleParts);
    }

    protected function getLocationId(string $juri): int
    {
        $slug = $juri !== 'us' ? 'us-' . $juri : 'us';
        /** @var Location|null */
        $location = Location::where('slug', $slug)->first();

        return $location?->id ?? 384;
    }

    protected function removeOldVersions(DomCrawler $domCrawler): void
    {
        $delete = [];
        foreach ($domCrawler->filterXPath('//code/version[text() != "1"]') as $versionNode) {
            /** @var DOMElement */
            $codeEl = $versionNode->parentNode;
            $thisVersion = (int) $versionNode->textContent;
            $sibling = $codeEl->nextElementSibling;

            if ($sibling) {
                foreach ($sibling->childNodes as $codeChild) {
                    if (strtolower($codeChild->nodeName) !== 'version') {
                        continue;
                    }
                    $nextVersion = (int) $codeChild->textContent;
                    // if the sibling code has a version number that's bigger, then we know we need to delete this one
                    if ($nextVersion > $thisVersion) {
                        $delete[] = $codeEl;
                    }
                }
            }
        }

        foreach ($delete as $node) {
            $node->parentNode?->removeChild($node);
        }

        // remove all version tags
        foreach ($domCrawler->filterXPath('//code/version') as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    protected function replaceImageAndFileLinks(string $html): string
    {
        // removes dates from image and file links
        $regex = '/(https:\/\/img\.norma\.com\/cmdata\/)((?:[a-z]+\/)+)(20[0-9]{2}\/)([^\'\"]+)/';

        /** @var string */
        return preg_replace($regex, '$1$2$4', $html);
    }

    protected function createTocItemsFromXml(DomCrawler $domCrawler, Doc $doc): void
    {
        /** @var DOMElement */
        $rootNode = $domCrawler->filterXPath('//codeheader/code[@type="Root"]')->getNode(0);
        $this->_createTocItemsFromXml($rootNode, $doc);
    }

    /**
     * Walks through the DOM recursively and creates ToC items.
     *
     * @param DOMElement        $node
     * @param Doc               $doc
     * @param int               $docPosition
     * @param int               $position
     * @param TocItem|null|null $parent
     *
     * @return void
     */
    protected function _createTocItemsFromXml(
        DOMElement $node,
        Doc $doc,
        int &$docPosition = 0,
        int $position = 0,
        ?TocItem $parent = null,
    ): void {
        $tocItem = null;
        if (strtolower($node->getAttribute('type')) !== 'root') {
            $docPosition++;
            $label = $this->getTocItemLabel($node);
            $tocItem = new TocItem([
                'source_unique_id' => ($parent ? $parent->source_unique_id . '__' : '') . $this->getNumberForCode($node),
                'crawl_id' => $doc->crawl_id,
                'doc_id' => $doc->id,
                'content_resource_id' => $this->getContentResourceForTocItem($node)?->id,
                'label' => strlen($label) > 300 ? Str::limit($label, 300, '...') : $label,
                'parent_id' => $parent?->id,
                'level' => $parent ? $parent->level + 1 : 1,
                'position' => $position,
                'doc_position' => $docPosition,
            ]);
            if (!is_null($parent)) {
                $tocItem->parent()->associate($parent);
            }
            $tocItem->doc()->associate($doc);

            $tocItem->setUid();
            $tocItem->save();
        }

        $childPos = 0;
        foreach ($node->childNodes as $childNode) {
            /** @var DOMElement $childNode */
            if (strtolower($childNode->nodeName) !== 'code') {
                continue;
            }
            $this->_createTocItemsFromXml($childNode, $doc, $docPosition, $childPos, $tocItem);
            $childPos++;
        }
    }

    protected function getTocItemLabel(DOMElement $node): string
    {
        $type = '';
        if (
            !in_array(strtolower($node->getAttribute('type')), ['act', 'sec2', 'codetitle', 'undesignated', 'unprefixed', 'group', 'subgroup'])
            && strtolower($node->getAttribute('style')) !== 'unprefixed'
        ) {
            $type = $node->getAttribute('type');
        }

        $parts = [];
        foreach ($node->childNodes as $child) {
            if (!in_array(strtolower($child->nodeName), ['number', 'name'])) {
                continue;
            }
            if (strtolower($child->nodeName) === 'number') {
                $text = ($type !== '' ? $type . ' ' : '') . $child->textContent;
            } else {
                $text = $child->textContent;
            }
            /** @var string $text */
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            $parts[] = $text;
        }

        // if there's no number or name, maybe there was a type though.
        if (empty($parts) && $type) {
            $parts[] = $type;
        }

        return implode(' - ', $parts);
    }

    protected function getNumberForCode(DOMElement $node): string
    {
        $number = null;
        foreach ($node->childNodes as $child) {
            if (!in_array(strtolower($child->nodeName), ['number'])) {
                continue;
            }
            $number = $child->textContent;
        }

        if (is_null($number)) {
            foreach ($node->childNodes as $child) {
                if (!in_array(strtolower($child->nodeName), ['name'])) {
                    continue;
                }
                $number = sha1($child->textContent);
            }
        }

        $number = is_null($number) ? '0' : $number;

        return $number;
    }

    /**
     * Transforms the XML to HTML and stores as content resource if it doesn't exist already.
     *
     * @param DOMElement $node
     *
     * @return ContentResource|null
     */
    protected function getContentResourceForTocItem(DOMElement $node): ?ContentResource
    {
        $contentNode = '';
        $numberNode = '';
        $nameNode = '';
        foreach ($node->childNodes as $child) {
            if (strtolower($child->nodeName) === 'content') {
                $contentNode = $child;
            }
            if (strtolower($child->nodeName) === 'number') {
                $numberNode = $child;
            }
            if (strtolower($child->nodeName) === 'name') {
                $nameNode = $child;
            }
        }
        $number = $numberNode->textContent ?? '';
        $title = $nameNode->textContent ?? '';
        if (!$contentNode) {
            return null;
        }

        /** @var DOMDocument $owner */
        $owner = $contentNode->ownerDocument;
        /** @var string $xml */
        $xml = $owner->saveHTML($contentNode);
        $xsldoc = new DOMDocument();
        $xsldoc->loadXML($this->getXsl());

        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($xsldoc);

        $xmlDoc = new DOMDocument();
        $xml = str_replace('&Sgr;', 'σ', $xml);
        $xmlDoc->loadXML($xml);

        $html = $xsl->transformToXML($xmlDoc);
        if (!$html) {
            return null;
        }

        $html = '<p><strong>' . trim($number . ' ' . $title) . '</strong></p>' . $html;

        $html = str_replace('<del', '<span', $html);
        $html = str_replace('</del>', '</span>', $html);

        $html = $this->replaceImageAndFileLinks($html);

        return app(ContentResourceStore::class)->storeResource($html, 'text/html');
    }

    protected function replaceHtmlEntities(string $xml): string
    {
        gc_collect_cycles();

        // remove any strange editor xml
        $xml = str_replace(['<?Pub Caret?>'], '', $xml);
        /** @var string $xml */
        $xml = preg_replace('/<\?Pub.*?\?>/', '', $xml);
        /** @var string $xml */
        $xml = preg_replace('/<\?xm\-replace_text.*?\?>/', '', $xml);

        // preserve these from entity decoding
        $xml = str_replace(['&amp;', '&#38;', '&AMP;'], '&amp#####;', $xml);
        $xml = str_replace(['&lt;', '&LT;'], '&lt#####;', $xml);
        $xml = str_replace(['&gt;', '&GT;'], '&gt#####;', $xml);
        $xml = str_replace(['&quot;', '&QUOT;'], '&quot#####;', $xml);
        /** @var string $xml */
        $xml = preg_replace_callback('/\&[a-zA-Z1-9]{2,35};/', function ($matches) {
            return html_entity_decode($matches[0]);
        }, $xml);

        $xml = str_replace('&amp#####;', '&amp;', $xml);
        $xml = str_replace('&lt#####;', '&lt;', $xml);
        $xml = str_replace('&gt#####;', '&gt;', $xml);
        $xml = str_replace('&quot#####;', '&quot;', $xml);

        return $xml;
    }

    // protected function findLevel(DOMNode $node, int $level = 1): int
    // {
    //     /** @var DOMElement */
    //     $parent = $node->parentNode;
    //     if (strtolower($parent->nodeName) === 'code' && strtolower($parent->getAttribute('type')) !== 'root') {
    //         return $this->findLevel($parent, $level + 1);
    //     }
    //     return $level;
    // }

    public function getXsl(): string
    {
        $filePath = '.' . DIRECTORY_SEPARATOR
            . 'app' . DIRECTORY_SEPARATOR
            . 'Services' . DIRECTORY_SEPARATOR
            . 'Arachno' . DIRECTORY_SEPARATOR
            . 'Crawlers' . DIRECTORY_SEPARATOR
            . 'Sources' . DIRECTORY_SEPARATOR
            . 'Us' . DIRECTORY_SEPARATOR
            . 'CasemakerTrans.xsl';
        $xsl = file_get_contents($filePath);
        if (!$xsl) {
            return '';
        }

        return $xsl;
    }

    /**
     * @param Crawl  $crawl
     * @param string $dir
     *
     * @return void
     */
    protected function parseDirForXmlFiles(Crawl $crawl, string $dir): void
    {
        foreach ($this->getFilesListing($dir) as $filename) {
            $name = Str::afterLast($filename, '/');
            // $this->addFileToFrontier($crawl->id, $name);
            $this->createCatalogueDocFromFile($crawl, $name);
        }
    }

    /**
     * @param Crawl  $crawl
     * @param string $juriDirName
     *
     * @return void
     */
    protected function crawlAdminAndStatForCatalogue(Crawl $crawl, string $juriDirName): void
    {
        $year = $this->getMaxYear($juriDirName);
        $dir = sprintf('%s/%s/ADMIN/XML', $juriDirName, $year);
        if ($this->checkIfDirectoryExists($dir)) {
            $this->parseDirForXmlFiles($crawl, $dir);
        } else {
            $this->parseDirForXmlFiles($crawl, sprintf('%s/%s/ADMIN/XML', $juriDirName, $year - 1));
        }

        $dir = sprintf('%s/%s/STAT/XML', $juriDirName, $year);
        if ($this->checkIfDirectoryExists($dir)) {
            $this->parseDirForXmlFiles($crawl, $dir);
        } else {
            $this->parseDirForXmlFiles($crawl, sprintf('%s/%s/STAT/XML', $juriDirName, $year - 1));
        }
    }

    /**
     * @param Crawl $crawl
     *
     * @return void
     */
    public function findChanges(Crawl $crawl): void
    {
        foreach ($this->getFilesListing('1Dated') as $filename) {
            if (!Str::of($filename)->endsWith('Finished.txt')) {
                continue;
            }
            // e.g. "1Dated/ms_admin_2022_20220801-1-1-Publication-Finished.txt"
            $changeVersion = str_replace(['1Dated/', '-1-1-Publication-Finished.txt'], '', $filename);
            // should leave us with ms_admin_2022_20220801

            if (!Str::of($changeVersion)->startsWith(strtolower($this->jurisdictionCode))) {
                continue;
            }

            if (CasemakerChange::where('title', $changeVersion)->exists()) {
                continue;
            }

            // $name = e.g. ms_admin_2022_20220801
            [$jurisdiction, $type, $year] = explode('_', $changeVersion);
            $folder = $this->getFileFolderFromNameParts($jurisdiction, $type, (int) $year, true);
            if (!$this->checkIfDirectoryExists($folder)) {
                continue;
            }
            $uniqueIds = [];
            foreach ($this->getFilesListing($folder) as $changedFileName) {
                // e.g. WV/2022/ADMIN/XML/PublishedChanges/Changes/wv-2022-admin-agency0178.xml
                $name = (string) Str::of($changedFileName)
                    ->after('PublishedChanges/Changes/');
                $uniqueIds[] = $this->filenameToUniqueId($name);
            }

            $this->arachno->fetchChangedDocs($uniqueIds, $crawl->crawler->source_id ?? 144);

            CasemakerChange::create(['title' => $changeVersion]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getJurisdictionConfig(): array
    {
        return [
            'ak-admin' => ['name' => 'Alaska Administrative Code'],
            'ak-stat' => ['name' => 'Alaska Statutes'],
            'al-admin' => [],
            'al-stat' => [],
            'ar-admin' => [],
            'ar-stat' => [],
            'az-admin' => ['name' => 'Arizona Administrative Code'],
            // 'ar-stat' => ['multi' => true, 'type' => 'Chapter'],
            // 'az-admin' => ['multi' => true, 'level' => 2, 'ordering' => false],
            'az-stat' => ['name' => 'Arizona Statutes'],
            'ca-admin' => ['name' => 'California Code Of Regulations'],
            'ca-stat' => ['name' => 'California Codes'],
            'cd-admin' => ['name' => 'District of Columbia Administrative Code'],
            'cd-stat' => ['name' => 'District of Columbia Statutes'],
            // 'cd-stat' => ['multi' => true, 'level' => 3],
            'co-admin' => ['name' => 'Colorado Administrative Code'],
            'co-stat' => ['name' => 'Colorado Statutes'],
            'ct-admin' => [],
            'ct-stat' => [],
            'de-admin' => ['name' => 'Delaware Administrative Code'],
            'de-stat' => ['name' => 'Delaware Statutes'],
            'fl-admin' => ['name' => 'Florida Administrative Department'],
            'fl-stat' => ['name' => 'Florida Statutes'],
            'ga-admin' => [],
            'ga-stat' => [],
            'hi-admin' => ['name' => 'Hawaii Administrative Rules'],
            'hi-stat' => ['name' => 'Hawaii Statutes'],
            'ia-admin' => ['name' => 'Iowa Administrative Code'],
            'ia-stat' => ['name' => 'Iowa Statutes'],
            'id-admin' => ['name' => 'Idaho Administrative Code'],
            'id-stat' => ['name' => 'Idaho Statutes'],
            'il-admin' => ['name' => 'Illinois Administrative Code'],
            'il-stat' => ['name' => 'Illinois Compiled Statutes'],
            'in-admin' => ['name' => 'Indiana Administrative Code'],
            'in-stat' => ['name' => 'Indiana Statutes'],
            'ks-admin' => [],
            'ks-stat' => [],
            'ky-admin' => [],
            'ky-stat' => [],
            'la-admin' => [],
            'la-stat' => [],
            'ma-admin' => ['name' => 'Massachusetts Administrative Departments'],
            'ma-stat' => ['name' => 'Massachusetts Statutes'],
            'md-admin' => ['name' => 'Maryland Administrative Code'],
            'md-stat' => ['name' => 'Maryland Statutes'],
            'me-admin' => ['name' => 'Maine Administrative Rules'],
            'me-stat' => ['name' => 'Maine Statutes'],
            'mi-admin' => [],
            'mi-stat' => [],
            'mn-admin' => ['name' => 'Minnesota Administrative Rules'],
            'mn-stat' => ['name' => 'Minnesota Statutes'],
            'mo-admin' => [],
            'mo-stat' => [],
            'ms-admin' => [],
            'ms-stat' => [],
            'mt-admin' => ['name' => 'Montana Administrative Code'],
            'mt-stat' => ['name' => 'Montana Statutes'],
            'nc-admin' => [],
            'nc-stat' => [],
            'nd-admin' => ['name' => 'North Dakota Administrative Code'],
            'nd-stat' => ['name' => 'North Dakota Statutes'],
            'ne-admin' => ['name' => 'Nebraska Administrative Rules and Regulations'],
            'ne-stat' => ['name' => 'Nebraska Statutes'],
            'nh-admin' => ['name' => 'New Hampshire Administrative Rules'],
            'nh-stat' => ['name' => 'New Hampshire Statutes'],
            'nj-admin' => ['name' => 'New Jersey Administrative Code'],
            'nj-stat' => ['name' => 'New Jersey Statutes'],
            'nm-admin' => ['name' => 'New Mexico Administrative Code'],
            'nm-stat' => ['name' => 'New Mexico Statutes'],
            'nv-admin' => ['name' => 'Nevada Administrative Code'],
            'nv-stat' => ['name' => 'Nevada Statutes'],
            'ny-admin' => ['name' => 'New York Codes, Rules and Regulations'],
            'ny-stat' => ['name' => 'New York Statutes'],
            'ny-city' => ['name' => 'New York City Rules'],
            'oh-admin' => ['name' => 'Ohio Administrative Code'],
            'oh-stat' => ['name' => 'Ohio Statutes'],
            'ok-admin' => [],
            'ok-stat' => [],
            'or-admin' => ['name' => 'Oregon Administrative Code'],
            'or-stat' => ['name' => 'Oregon Statutes'],
            'pa-admin' => ['name' => 'Pennsylvania Code (Rules and Regulations)'],
            'pa-stat' => ['name' => 'Pennsylvania Statutes'],
            'pr-stat' => [],
            'ri-admin' => ['name' => 'Rhode Island Administrative Regulations'],
            'ri-stat' => ['name' => 'Rhode Island Statutes'],
            'sc-admin' => [],
            'sc-stat' => [],
            'sd-admin' => ['name' => 'South Dakota Administrative Code'],
            'sd-stat' => ['name' => 'South Dakota Statutes'],
            'tn-admin' => [],
            'tn-stat' => [],
            'tx-admin' => ['name' => 'Texas Administrative Code'],
            'tx-stat' => ['name' => 'Texas Statutes'],
            'us-stat' => ['name' => 'United States Statutes'],
            'us-admin' => ['name' => 'Code of Federal Regulations'], // USCFR
            'ut-admin' => ['name' => 'Utah Administrative Code'], // Utah Admin Code
            'ut-stat' => ['name' => 'Utah Statutes'],
            'va-admin' => ['name' => 'Virginia Administrative Code'],
            'va-stat' => ['name' => 'Virginia Statutes'],
            'vt-admin' => ['name' => 'Vermont Administrative Code'],
            'vt-stat' => ['name' => 'Vermont Statutes'],
            'wa-admin' => ['name' => 'Washington Administrative Code'],
            'wa-stat' => ['name' => 'Washington Statutes'],
            'wi-admin' => ['name' => 'Wisconsin Administrative Code'],
            'wi-stat' => ['name' => 'Wisconsin Statutes'],
            'wv-admin' => ['name' => 'West Virginia Administrative Code'],
            'wv-stat' => ['name' => 'West Virginia Statutes'],
            'wy-admin' => [],
            'wy-stat' => [],
        ];
    }
}
