<?php

namespace App\Services\Arachno\Support;

use DOMNode;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Helper function to have equivalent of:
 * https://developer.mozilla.org/en-US/docs/Web/API/Element/closest
 * But different, as it returns a DOM Crawler object, as that's what you would want to use
 * most times anyway. You can then run further queries on it, or do $crawler->getNode(0) to get the matching DOMNode.
 */
class DomClosest
{
    public function closest(DOMNode $node, string $selector): ?Crawler
    {
        $parentNode = $node->parentNode;
        while ($parentNode) {
            $dom = new Crawler($parentNode);
            $node = $dom->filter($selector)->first()->getNode(0);
            if ($node && $parentNode->isSameNode($node)) {
                return $dom;
            }
            $parentNode = $parentNode->parentNode;
        }

        return null;
    }
}
