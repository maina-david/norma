<?php

namespace App\Services\Arachno\Parse;

use App\Services\Arachno\Frontier\PageCrawl;
use Generator;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class TocItemFinder
{
    /**
     * @param TreeWalker $treeWalker
     */
    public function __construct(protected TreeWalker $treeWalker)
    {
    }

    /**
     * @param DomCrawler    $crawler
     * @param callable|null $criteria
     * @param callable|null $extractLabel
     * @param callable|null $extractLink
     * @param callable|null $extractUniqueId
     * @param callable|null $determineDepth
     *
     * @return Generator<TocItemDraft>
     */
    public function findItems(
        DomCrawler $crawler,
        ?callable $criteria = null,
        ?callable $extractLabel = null,
        ?callable $extractLink = null,
        ?callable $extractUniqueId = null,
        ?callable $determineDepth = null
    ): Generator {
        $criteria = $criteria ?? function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');

            return strtolower($nodeDTO['node']->nodeName) === 'a' && $href && $href !== '#';
        };

        $extractLabel = $extractLabel ?? fn ($nodeDto) => $nodeDto['node']->textContent;
        $extractLink = $extractLink ?? fn ($nodeDto) => $nodeDto['node']->getAttribute('href');

        $nodesList = [];
        foreach ($crawler as $topLevelNode) {
            foreach ($this->treeWalker->walk($topLevelNode) as $nodeDTO) {
                if (call_user_func($criteria, $nodeDTO)) {
                    /** @var string */
                    $label = call_user_func($extractLabel, $nodeDTO);
                    // @phpstan-ignore-next-line
                    $nodeDTO['label'] = trim(preg_replace('/\s+/', ' ', preg_replace('/\r|\n/', '', $label)));
                    /** @var string */
                    $href = call_user_func($extractLink, $nodeDTO);
                    // @phpstan-ignore-next-line
                    $nodeDTO['href'] = trim(preg_replace('/\s+/', ' ', preg_replace('/\r|\n/', '', $href)));
                    if ($extractUniqueId) {
                        /** @var string $id */
                        $id = call_user_func($extractUniqueId, $nodeDTO);
                        $nodeDTO['source_unique_id'] = trim(preg_replace('/\s+/', ' ', preg_replace('/\r|\n/', '', $id) ?? '') ?? '');
                    }

                    $nodesList[] = $nodeDTO;
                }
            }
        }

        $nodesList = $this->normalizeNodesList($nodesList, $determineDepth);
        foreach ($nodesList as $item) {
            yield new TocItemDraft($item);
        }
    }

    /**
     * @param array<array<mixed>> $nodesList
     * @param callable|null       $determineDepth
     *
     * @return array<array<string,mixed>>
     */
    private function normalizeNodesList(array $nodesList, ?callable $determineDepth = null): array
    {
        // get a list of all available depths. e.g. 1, 3, 3, 3, 6 , 6, 3 ,3  => [1 => true, 3 => true, 6 => true]
        $depths = [];
        foreach ($nodesList as $ind => $nodeDto) {
            if (!is_null($determineDepth)) {
                $nodesList[$ind]['depth'] = call_user_func($determineDepth, $nodeDto);
            }

            $depths[$nodesList[$ind]['depth']] = true;
        }
        // then we'll have a unique, ordered list of available depths e.g. [1, 3, 6]. Their positions in the array will be the true level (+1)
        $depths = array_keys($depths);
        sort($depths);

        $newList = [];
        foreach ($nodesList as $nodeDto) {
            $nodeDto['level'] = array_search($nodeDto['depth'], $depths) + 1;
            unset($nodeDto['position']);

            $newList[] = $nodeDto;
        }

        return $newList;
    }

    // /**
    //  * @param array<mixed> $json
    //  *
    //  * @return Generator<TocItemDraft>
    //  */
    // public function findItemsJson(array $json, callable $func, PageCrawl $pageCrawl): Generator
    // {
    //     /** @var array<TocItemDraft> */
    //     $items = call_user_func_array($func, [$json, $pageCrawl]);
    //     foreach ($items as $item) {
    //         yield $item;
    //     }
    // }
}
