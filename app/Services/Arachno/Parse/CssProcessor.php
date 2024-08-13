<?php

namespace App\Services\Arachno\Parse;

use Illuminate\Support\Str;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;

class CssProcessor
{
    public const CSS_LIBRYO_FULL_TEXT_CLASS = 'libryo-full-text';
    public const CSS_BORDER_COLOR = '#aaa';

    /**
     * @param string        $css
     * @param array<string> $removeSelectors
     *
     * @return string
     */
    public function process(string $css, array $removeSelectors = []): string
    {
        $parser = new Parser($css);
        $cssDocument = $parser->parse();

        $this->removeImports($cssDocument);

        foreach ($cssDocument->getAllDeclarationBlocks() as $block) {
            $this->removeSelectors($removeSelectors, $block, $cssDocument);
            if (count($block->getSelectors()) === 0) {
                $cssDocument->remove($block);
                continue;
            }

            $this->processSelectors($block, $cssDocument);

            $this->removeRules($block);
            $this->replaceRules($block);

            $this->removeBlockIfEmpty($block, $cssDocument);
        }

        return $cssDocument->render(OutputFormat::createCompact());
    }

    /**
     * @param array<string>    $removeSelectors
     * @param DeclarationBlock $block
     * @param Document         $cssDocument
     *
     * @return Document
     */
    public function removeSelectors(
        array $removeSelectors,
        DeclarationBlock $block,
        Document $cssDocument
    ): Document {
        foreach ($removeSelectors as $selectorStr) {
            if (Str::startsWith($selectorStr, '/')) {
                foreach ($block->getSelectors() as $selector) {
                    $str = $selector instanceof Selector ? $selector->getSelector() : $selector;
                    if (Str::of($str)->test($selectorStr)) {
                        $block->removeSelector($selector);
                    }
                }
                continue;
            }
            $block->removeSelector($selectorStr);
        }

        return $cssDocument;
    }

    /**
     * @param Document $cssDocument
     *
     * @return void
     */
    public function removeImports(Document $cssDocument): void
    {
        foreach ($cssDocument->getContents() as $block) {
            if ($block instanceof Import) {
                $cssDocument->remove($block);
            }
        }
    }

    /**
     * @param DeclarationBlock $block
     * @param Document         $cssDocument
     *
     * @return void
     */
    public function processSelectors(DeclarationBlock $block, Document $cssDocument): void
    {
        // Loop over all selector parts (the comma-separated strings in a
        // selector) and prepend the ID.
        foreach ($block->getSelectors() as $selector) {
            $selector = is_string($selector) ? new Selector($selector) : $selector;
            $str = $selector->getSelector();
            if (strtolower($str) === 'body') {
                $this->replaceBody($selector);
                continue;
            }
            if (strtolower($str) === 'html') {
                $cssDocument->remove($block);
                continue;
            }
            $this->prepend($selector);
        }
    }

    /**
     * @param Selector $selector
     *
     * @return Selector
     */
    public function replaceBody(Selector $selector): Selector
    {
        $libryoClass = '.' . static::CSS_LIBRYO_FULL_TEXT_CLASS;
        $selector->setSelector($libryoClass);

        return $selector;
    }

    /**
     * @param Selector $selector
     *
     * @return Selector
     */
    public function prepend(Selector $selector): Selector
    {
        $libryoClass = '.' . static::CSS_LIBRYO_FULL_TEXT_CLASS;
        $selector->setSelector($libryoClass . ' ' . $selector->getSelector());

        return $selector;
    }

    /**
     * @param RuleSet $ruleSet
     *
     * @return RuleSet
     */
    public function removeRules(RuleSet $ruleSet): RuleSet
    {
        foreach ($this->getRemoveRules() as $rule) {
            $ruleSet->removeRule($rule);
        }

        return $ruleSet;
    }

    /**
     * @param RuleSet $ruleSet
     *
     * @return RuleSet
     */
    public function replaceRules(RuleSet $ruleSet): RuleSet
    {
        foreach ($ruleSet->getRules() as $rule) {
            $this->replaceRulesInRule($rule);
        }

        return $ruleSet;
    }

    /**
     * @param Rule $rule
     *
     * @return Rule
     */
    public function replaceRulesInRule(Rule $rule): Rule
    {
        $borderRegex = '/([^\s]+[\s ]+[^\s]+[\s ]+)([^\s ]+)/';
        $borderReplace = '$1' . static::CSS_BORDER_COLOR;
        $replace = [
            ['rule' => 'border', 'pattern' => $borderRegex, 'replace' => $borderReplace],
            ['rule' => 'border-left', 'pattern' => $borderRegex, 'replace' => $borderReplace],
            ['rule' => 'border-right', 'pattern' => $borderRegex, 'replace' => $borderReplace],
            ['rule' => 'border-top', 'pattern' => $borderRegex, 'replace' => $borderReplace],
            ['rule' => 'border-bottom', 'pattern' => $borderRegex, 'replace' => $borderReplace],
        ];
        foreach ($replace as $repl) {
            if ($repl['rule'] !== $rule->getRule()) {
                continue;
            }
            // @phpstan-ignore-next-line
            $newValue = preg_replace($repl['pattern'], $repl['replace'], $rule->getValue());
            $rule->setValue($newValue);
        }

        return $rule;
    }

    /**
     * @param RuleSet  $block
     * @param Document $cssDocument
     *
     * @return Document
     */
    public function removeBlockIfEmpty(RuleSet $block, Document $cssDocument): Document
    {
        if (count($block->getRules()) === 0) {
            $cssDocument->remove($block);
        }

        return $cssDocument;
    }

    /**
     * @param string $css
     *
     * @return array<string>
     */
    public function extractImportLinks(string $css): array
    {
        $parser = new Parser($css);
        $cssDocument = $parser->parse();

        $links = [];
        foreach ($cssDocument->getContents() as $block) {
            if ($block instanceof Import) {
                $links[] = $block->getLocation()->getURL()->getString();
            }
        }

        return $links;
    }

    /**
     * @return array<string>
     */
    public function getRemoveRules(): array
    {
        // Note that the added dash will make this remove all rules starting with
        // `font-` (like `font-size`, `font-weight`, etc.) as well as a potential
        // `font-rule`.
        // $oRuleSet->removeRule('font-');

        return [
            'animation',
            'animation-',
            'backface-visibility',
            'background',
            'background-',
            'block-size',
            'border-bottom-color',
            'border-collapse',
            'border-color',
            'border-left-color',
            'border-image-repeat',
            'border-image-slice',
            'border-image-width',
            'border-right-color',
            'border-top-color',
            'box-sizing',
            'caption-side',
            'caret-color',
            'clip-rule',
            'color',
            'color-interpolation',
            'color-interpolation-filters',
            'column-rule-color',
            'cursor',
            'empty-cells',
            'flood-color',
            'flood-opacity',
            'font',
            'font-family',
            'font-size',
            'font-stretch',
            'grid-auto-flow',
            'height',
            'hyphens',
            'image-orientation',
            'inline-size',
            'letter-spacing',
            'lighting-color',
            'line-height',
            'margin-inline-end',
            'margin-inline-start',
            'max-height',
            'min-height',
            'mask-type',
            'mso-style-name',
            'offset-rotate',
            'opacity',
            'orphans',
            'outline',
            'outline-color',
            'perspective-origin',
            'ruby-position',
            'stop-color',
            'stop-opacity',
            'stroke-linecap',
            'stroke-linejoin',
            'stroke-miterlimit',
            'stroke-opacity',
            'stroke-width',
            'tab-size',
            'text-anchor',
            'text-size-adjust',
            'transform-',
            'transition-',
            'visibility',
            'widows',
            'zoom',
        ];
    }
}
