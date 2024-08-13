<?php

namespace App\Services\Html;

use DOMElement;
use DOMNode;
use Symfony\Component\DomCrawler\Crawler;

class ExtractBetween
{
    public function betweenIds(string $html, string $id1, ?string $id2 = null): string
    {
        $crawler = new Crawler($html);
        /** @var DOMElement */
        $bodyNode = $crawler->filter('body')->getNode(0);
        /** @var DOMElement|null */
        $firstNode = $crawler->filter('#' . $id1)->getNode(0);
        if (!$firstNode) {
            $firstNode = $crawler->filter('a[name="' . $id1 . '"]')->getNode(0);
        }
        $firstNode = $firstNode ?? $bodyNode;
        /** @var DOMElement|null */
        $secondNode = $id2 ? $crawler->filter('#' . $id2)->getNode(0) : $bodyNode;
        if ($id2 && !$secondNode) {
            $secondNode = $crawler->filter('a[name="' . $id2 . '"]')->getNode(0);
        }
        $secondNode = $secondNode ?? $bodyNode;

        return $this->extract($bodyNode, $firstNode, $secondNode);
    }

    public function betweenSelectors(string $html, string $selector1, ?string $selector2 = null): string
    {
        $crawler = new Crawler($html);
        /** @var DOMElement */
        $bodyNode = $crawler->filter('body')->getNode(0);
        /** @var DOMElement|null */
        $firstNode = $crawler->filter($selector1)->getNode(0);
        $firstNode = $firstNode ?? $bodyNode;
        /** @var DOMElement|null */
        $secondNode = $selector2 ? $crawler->filter($selector2)->getNode(0) : $bodyNode;
        $secondNode = $secondNode ?? $bodyNode;

        return $this->extract($bodyNode, $firstNode, $secondNode);
    }

    private function extract(DOMNode $bodyNode, DOMNode $firstNode, DOMNode $secondNode): string
    {
        $firstNodePath = $this->getPath($firstNode, $bodyNode);
        $secondNodePath = $this->getPath($secondNode, $bodyNode);

        /** @var DOMNode */
        $commonNode = $this->findCommonParentNode($firstNodePath, $secondNodePath) ?? $bodyNode;
        $fragmentCrawler = new Crawler($commonNode);

        $previousNode = $this->getPreviousNode($firstNode, $commonNode);
        while ($previousNode && !$previousNode->isSameNode($commonNode)) {
            $previousNode->parentNode?->removeChild($previousNode);
            $previousNode = $this->getPreviousNode($firstNode, $commonNode);
        }
        $nextNode = $this->getNextNode($secondNode, $commonNode);
        while ($nextNode && !$nextNode->isSameNode($commonNode)) {
            $nextNode->parentNode?->removeChild($nextNode);
            $nextNode = $this->getNextNode($secondNode, $commonNode);
        }

        if (!$secondNode->isSameNode($bodyNode)) {
            $secondNode->parentNode?->removeChild($secondNode);
        }

        if ($fragmentCrawler->getNode(0)?->isSameNode($bodyNode)) {
            return str_replace(['<body>', '</body>'], '', $fragmentCrawler->outerHtml());
        }

        return $fragmentCrawler->outerHtml();
    }

    /**
     * @param DOMNode $domNode
     * @param DOMNode $bodyNode
     *
     * @return array<DOMNode>
     */
    public function getPath(DOMNode $domNode, DOMNode $bodyNode): array
    {
        $path = [$domNode];
        $node = $domNode;
        while (!$bodyNode->isSameNode($node)) {
            /** @var DOMNode $node */
            $node = $node->parentNode;
            $path[] = $node;
        }

        return $path;
    }

    /**
     * @param array<DOMNode> $firstNodePath
     * @param array<DOMNode> $secondNodePath
     *
     * @return DOMNode|null
     */
    public function findCommonParentNode(array $firstNodePath, array $secondNodePath): ?DOMNode
    {
        foreach ($firstNodePath as $node) {
            foreach ($secondNodePath as $node2) {
                if ($node->isSameNode($node2)) {
                    return $node;
                }
            }
        }

        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }

    protected function getNextNode(DOMNode $node, DOMNode $commonNode): ?DOMNode
    {
        $n = $node;
        while ($n && !$n->isSameNode($commonNode) && !$n->nextSibling) {
            $n = $n->parentNode;
        }

        return $n?->nextSibling;
    }

    protected function getPreviousNode(DOMNode $node, DOMNode $commonNode): ?DOMNode
    {
        $n = $node;
        while ($n && !$n->isSameNode($commonNode) && !$n->previousSibling) {
            $n = $n->parentNode;
        }

        return $n?->previousSibling;
    }
}
