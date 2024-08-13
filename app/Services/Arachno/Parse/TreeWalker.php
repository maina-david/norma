<?php

namespace App\Services\Arachno\Parse;

use DOMNode;
use Generator;

class TreeWalker
{
    /**
     * @param DOMNode $domNode
     * @param int     $depth
     * @param int     $position
     *
     * @return Generator<array<string,DOMNode|int>>
     */
    public function walk(DOMNode $domNode, int $depth = 0, int $position = 0): Generator
    {
        yield $this->makeDTO($domNode, $depth, $position);

        foreach ($domNode->childNodes as $ix => $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            foreach ($this->walk($child, $depth + 1, $ix) as $dto) {
                yield $dto;
            }
        }
    }

    /**
     * @param DOMNode $node
     * @param int     $depth
     * @param int     $position
     *
     * @return array<string,DOMNode|int>
     */
    private function makeDTO(DOMNode $node, int $depth, int $position): array
    {
        return [
            'node' => $node,
            'depth' => $depth,
            'position' => $position,
        ];
    }
}
