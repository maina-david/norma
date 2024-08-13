<?php

namespace App\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\PageCrawl;
use Illuminate\Support\Arr;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Throwable;

class DomQueryRunner
{
    /**
     * @param StringTransformer $stringTransformer
     */
    public function __construct(protected StringTransformer $stringTransformer)
    {
    }

    /**
     * @param DomQuery   $query
     * @param DomCrawler $domCrawler
     *
     * @throws InvalidQueryException
     *
     * @return DomCrawler
     */
    public function runQuery(DomQuery $query, DomCrawler $domCrawler): DomCrawler
    {
        try {
            return $query->isTypeCss()
                ? $domCrawler->filter($query['query'])
                : $domCrawler->filterXPath($query['query']);
            // @codeCoverageIgnoreStart
        } catch (Throwable $th) {
            throw new InvalidQueryException(sprintf('Invalid %s DomQuery: %s', $query['type']->value ?? 'xpath', $query['query']));
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param DomQuery  $query
     * @param PageCrawl $pageCrawl
     *
     * @return bool
     */
    public function queryMatches(
        DomQuery $query,
        PageCrawl $pageCrawl,
    ): bool {
        $domCrawler = $pageCrawl->domCrawler;
        $pageUrl = $pageCrawl->pageUrl;
        if ($query->isNested()) {
            $results = [];
            $operator = $query['operator'] ?? 'or';
            foreach ($query['query'] as $q) {
                $results[] = $this->queryMatches($q, $pageCrawl);
            }
            if (strtolower($operator) === 'and') {
                return !in_array(false, $results, true);
            }

            // 'or' operator
            return in_array(true, $results, true);
        }
        if ($query->isTypeFunc()) {
            $call = $query['query'];

            return $call($pageCrawl);
        }
        // if (isset($query['url_regex']) && preg_match_all($query['url_regex'], $pageUrl->url) === false) {
        //     //@codeCoverageIgnoreStart
        //     return false;
        //     //@codeCoverageIgnoreEnd
        // }
        if ($query->isUrlSource()) {
            return preg_match_all($query['query'], $pageUrl->url) > 0;
        }
        if ($query->isTypeJson()) {
            return !is_null($this->getTextByQuery($query, $pageCrawl));
        }

        return count($this->runQuery($query, $domCrawler)) > 0;
    }

    /**
     * @param DomQuery             $query
     * @param PageCrawl            $pageCrawl
     * @param UrlFrontierLink|null $pageUrl
     *
     * @return string|null
     */
    public function getTextByQuery(
        DomQuery $query,
        PageCrawl $pageCrawl,
        ?UrlFrontierLink $pageUrl = null
    ): ?string {
        $domCrawler = $pageCrawl->domCrawler;
        $pageUrl = $pageUrl ?? $pageCrawl->pageUrl;
        if ($query->isUrlSource()) {
            $text = $pageCrawl->getFinalRedirectedUrl();
        } elseif ($query->isAnchorTextSource()) {
            $text = $pageUrl->anchor_text;
        } elseif ($query->isTypeText()) {
            $text = $query['query'];
        } elseif ($query->isTypeJson()) {
            $responseContent = $pageCrawl->contentCache?->response_body ?? '{}';
            $json = json_decode($responseContent, true);

            if (is_null($json)) {
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
            }

            $text = Arr::get($json, $query['query']);
        } elseif ($query->isTypeFunc()) {
            $call = $query['query'];

            $text = $call($pageCrawl);
        } else {
            $crawler = $this->runQuery($query, $domCrawler);
            if (count($crawler) === 0) {
                return null;
            }

            $texts = [];
            foreach ($crawler as $node) {
                $texts[] = $node->textContent;
            }

            if (isset($query['pre_glue_transforms'])) {
                foreach ($texts as &$txt) {
                    $txt = $this->stringTransformer->apply($txt, $query['pre_glue_transforms']);
                }
            }

            $glue = $query['glue'] ?? '';
            $text = implode($glue, $texts);
        }

        if ($query->isTypeRegex()) {
            $text = $this->extractTextWithRegexQuery($query, $text ?? '');
        }

        if (isset($query['transforms'])) {
            $text = $this->stringTransformer->apply($text ?? '', $query['transforms']);
        }

        return $text;
    }

    /**
     * Extracts all text that matches the given regex from the given text.
     *
     * @param DomQuery $query
     * @param string   $text
     *
     * @return string
     */
    public function extractTextWithRegexQuery(DomQuery $query, string $text): string
    {
        preg_match($query['query'], $text, $matches);
        if (count($matches) === 0) {
            return $text;
        }

        if (count($matches) === 1) {
            return $matches[0];
        }

        // there are parentheses in the regex - matches contains all matches within parentheses.
        // first value is full match, so remove that.
        array_shift($matches);

        // then just concatenate all matches
        return implode('', $matches);
    }

    /**
     * Can be used to test queries against a url.
     *
     * @codeCoverageIgnore
     *
     * @param DomQueryType $type
     * @param string       $query
     * @param string       $urlOrPath
     * @param string       $mimeType
     *
     * @return bool
     */
    public function testQuery(DomQueryType $type, string $query, string $urlOrPath, string $mimeType = 'text/html'): bool
    {
        $query = new DomQuery(['type' => $type, 'query' => $query]);
        $contents = file_get_contents($urlOrPath);
        if ($contents === false) {
            return false;
        }
        $domCrawler = new DomCrawler('');
        $domCrawler->addContent($contents, $mimeType);

        return count($this->runQuery($query, $domCrawler)) > 0;
    }
}
