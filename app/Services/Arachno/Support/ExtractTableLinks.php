<?php

namespace App\Services\Arachno\Support;

use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\DomQueryRunner;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use DOMNode;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Helper function to extract links from table rows together with meta data for docs.
 */
class ExtractTableLinks
{
    public function __construct(protected DomQueryRunner $domQueryRunner, protected LinkToUri $linkToUri)
    {
    }

    /**
     * @param PageCrawl               $pageCrawl
     * @param DomQuery                $rowQuery    The query that iterates over the table rows / containers that contain links
     * @param DomQuery                $linkQuery   Once within a table row, the query that extracts the link
     * @param array<string, DomQuery> $metaQueries Queries for other meta data in the format ['source_unique_id' => new DomQuery(...), 'title' => new DomQuery(...)]
     *
     * @return array<UrlFrontierLink>
     */
    public function extract(PageCrawl $pageCrawl, DomQuery $rowQuery, DomQuery $linkQuery, array $metaQueries): array
    {
        $rows = $this->domQueryRunner->runQuery($rowQuery, $pageCrawl->domCrawler);
        $links = [];
        foreach ($rows as $row) {
            $tr = new Crawler($row);
            /** @var DOMNode|null */
            $link = $this->domQueryRunner->runQuery($linkQuery, $tr)->getNode(0);
            if (is_null($link)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $anchorText = '';
            if ($link instanceof DOMElement && $link->hasAttribute('href')) {
                $href = $link->getAttribute('href');
                $anchorText = $link->textContent;
            } else {
                $href = $link->textContent;
            }
            $uri = $this->linkToUri->convert($href, $pageCrawl->getFinalRedirectedUrl());

            $trPageCrawl = new PageCrawl($tr, $pageCrawl->pageUrl, $pageCrawl->crawlerSettings);
            $docMeta = !empty($metaQueries) ? new DocMetaDto() : null;
            foreach ($metaQueries as $property => $query) {
                $text = $this->domQueryRunner->getTextByQuery($query, $trPageCrawl);
                if (!$text) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                if (in_array($property, ['work_date', 'effective_date'])) {
                    $format = 'Y-m-d';
                    if (str_contains($text, '|')) {
                        [$format, $text] = explode('|', $text);
                    }

                    try {
                        $value = Carbon::createFromFormat($format, $text);
                        // @codeCoverageIgnoreStart
                    } catch (InvalidFormatException $th) {
                        continue;
                    }
                // @codeCoverageIgnoreEnd
                } else {
                    $value = $text;
                }
                if (is_string($value)) {
                    $value = trim($text);
                }
                $docMeta[$property] = $value;
            }

            /** @var DocMetaDto|null $docMeta */
            $l = new UrlFrontierLink(['url' => (string) $uri, 'anchor_text' => $anchorText]);
            $l->_metaDto = $docMeta;
            $links[] = $l;
        }

        return $links;
    }
}
