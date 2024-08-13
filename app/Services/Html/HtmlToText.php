<?php

namespace App\Services\Html;

use App\Enums\Html\BlockElement;
use App\Enums\Html\InlineElement;
use App\Services\Arachno\Support\DomClosest;
use DOMElement;
use DOMNode;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * A simple html to text convertor that is mainly used for text analysis. So it's not strictly
 * correct visually with alignments (e.g. horizontal, vertical, tables, floats, css grid, flex etc..)
 * It ensures  that whitespaces are inserted between span and div elements  preventing cases where two words stick together.
 */
class HtmlToText
{
    public function __construct(protected DomClosest $domClosest)
    {
    }

    public function convert(string $html): string
    {
        $dom = $this->inlineBlocks($html);

        $text = '';
        try {
            $children = $dom->filter('body')->children();
            // @codeCoverageIgnoreStart
        } catch (InvalidArgumentException $e) {
            // if the body filter doesn't result in any nodes, the children method will throw an exception
            return '';
        }
        // @codeCoverageIgnoreEnd

        foreach ($children as $child) {
            // $text .= preg_replace('/\r?\n|\r/', ' ', $child->textContent) . '\n';
            $text .= $child->textContent . "\n";
        }

        $text = preg_replace('/[ \t]+/', ' ', $text);

        /** @var string */
        return $text;
    }

    public function inlineBlocks(string $html): Crawler
    {
        // replace all duplicate whitespaces with just one - also removes all line breaks
        /** @var string */
        $html = preg_replace('/\s+/', ' ', $html);
        $dom = new Crawler($html);

        $this->lineBreaksToParagraphs($dom);
        $this->wrapMixedInBlock($dom);
        $this->tablesToBlocks($dom);
        $this->padInlines($dom);
        $this->blocksToBody($dom);
        $this->removeEmpty($dom);

        return $dom;
    }

    public function wrapMixedInBlock(Crawler $dom): void
    {
        [$imploded, $implodedChildren] = $this->getBlocksXpath();
        // all block elements that have block elements as direct children AND text nodes as direct children.
        $xpath = sprintf('//*[(%s) and (%s) and child::text()]', $imploded, $implodedChildren);

        // all block elements that have block elements as direct children.
        // if those direct children have sibling nodes that are not block elements
        // then we'll wrap them in a block element
        foreach ($dom->filterXPath($xpath) as $parentNode) {
            $lastDiv = null;
            $next = $parentNode->childNodes[0];
            while ($next) {
                if ($next->nodeType === XML_TEXT_NODE || ($next->nodeType === XML_ELEMENT_NODE && !$this->isBlockNode($next))) {
                    if (!$lastDiv) {
                        /** @var DOMElement */
                        $lastDiv = $parentNode->ownerDocument?->createElement('div');
                        $parentNode->insertBefore($lastDiv, $next);
                    }
                    $lastDiv->appendChild($next);
                    $next = $lastDiv->nextSibling;
                } else {
                    $lastDiv = null;
                    $next = $next->nextSibling;
                }
            }
        }
    }

    protected function isBlockNode(DOMNode $node): bool
    {
        return in_array(strtolower($node->nodeName), BlockElement::options());
    }

    /**
     * Moves all block elements to the top of the DOM attached directly to the body element.
     *
     * @param Crawler $dom
     *
     * @return void
     */
    public function blocksToBody(Crawler $dom): void
    {
        [$imploded] = $this->getBlocksXpath();

        $xpath = sprintf('//*[%s]', $imploded);
        /** @var DOMElement */
        $bodyNode = $dom->filter('body')->getNode(0);
        foreach ($dom->filterXPath($xpath) as $block) {
            $bodyNode->appendChild($block);
        }
    }

    /**
     * Pad all inline elements with spaces so to ensure that whitespaces
     * are inserted between span and div elements preventing cases where
     * two words stick together.
     *
     * @param Crawler $dom
     *
     * @return void
     */
    public function padInlines(Crawler $dom): void
    {
        $imploded = $this->getInlinesXpath();

        $xpath = sprintf('//*[%s]', $imploded);
        foreach ($dom->filterXPath($xpath) as $inline) {
            /** @var DOMNode|null */
            $firstChild = $inline->childNodes[0];
            if (!$firstChild) {
                continue;
            }
            if ($inline->previousSibling) {
                /** @var DOMNode */
                $text = $inline->ownerDocument?->createTextNode(' ');
                $inline->insertBefore($text, $firstChild);
            }
            if ($inline->nextSibling) {
                /** @var DOMNode */
                $text = $inline->ownerDocument?->createTextNode(' ');
                $inline->appendChild($text);
            }
        }
    }

    /**
     * Removes empty elements.
     *
     * @param Crawler $dom
     *
     * @return void
     */
    public function removeEmpty(Crawler $dom): void
    {
        [$imploded] = $this->getBlocksXpath();

        $xpath = sprintf('//*[%s]', $imploded);
        foreach ($dom->filterXPath($xpath) as $block) {
            if (!$block->hasChildNodes() || trim($block->textContent) === '') {
                $block->parentNode?->removeChild($block);
            }
        }
    }

    /**
     * Removes empty elements.
     *
     * @param Crawler $dom
     *
     * @return void
     */
    public function lineBreaksToParagraphs(Crawler $dom): void
    {
        [$imploded] = $this->getBlocksXpath();

        $xpath = '//*[local-name()="br"]';
        foreach ($dom->filterXPath($xpath) as $br) {
            /** @var DOMElement */
            $para = $br->ownerDocument?->createElement('div');
            $br->parentNode?->insertBefore($para, $br);
            $br->parentNode?->removeChild($br);
        }
    }

    /**
     * Converts Table rows to blocks.
     *
     * @param Crawler $dom
     *
     * @return void
     */
    public function tablesToBlocks(Crawler $dom): void
    {
        [$imploded] = $this->getBlocksXpath();

        $selector = 'table tr';
        foreach ($dom->filter($selector) as $row) {
            $tableNode = $this->domClosest->closest($row, 'table')?->getNode(0);
            /** @var DOMElement $div */
            $div = $row->ownerDocument?->createElement('div');
            foreach ((new Crawler($row))->filter('td,th') as $cell) {
                /** @var DOMElement $span */
                $span = $row->ownerDocument?->createElement('span');

                while ($cellChild = $cell->childNodes->item(0)) {
                    $span->appendChild($cellChild);
                }

                $div->appendChild($span);
            }
            if (!$tableNode) {
                // @codeCoverageIgnoreStart
                $tableNode = $row->parentNode;
                // @codeCoverageIgnoreEnd
            }
            $tableNode?->parentNode?->insertBefore($div, $tableNode);
        }

        $xpath = '//*[local-name()="table"]';
        foreach ($dom->filterXPath($xpath) as $table) {
            $table->parentNode?->removeChild($table);
        }
    }

    /**
     * @return array<string>
     */
    protected function getBlocksXpath(): array
    {
        $blocks = [];
        $blockChildren = [];
        foreach (BlockElement::options() as $element) {
            $blocks[] = sprintf('local-name()="%s"', $element);
            $blockChildren[] = sprintf('child::%s', $element);
        }
        $imploded = implode(' or ', $blocks);
        $implodedChildren = implode(' or ', $blockChildren);

        return [$imploded, $implodedChildren];
    }

    protected function getInlinesXpath(): string
    {
        $inlines = [];
        foreach (InlineElement::options() as $element) {
            $inlines[] = sprintf('local-name()="%s"', $element);
        }

        return implode(' or ', $inlines);
    }
}
