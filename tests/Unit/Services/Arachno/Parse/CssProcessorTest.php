<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Services\Arachno\Parse\CssProcessor;
use Sabberworm\CSS\Parser;
use Tests\TestCase;

class CssProcessorTest extends TestCase
{
    public function testProcess(): void
    {
        $css = $this->getCss();
        $removeSelectors = [
            '.remove-this-selector table tr',
            '.remove-this-whole-block',
        ];
        $css = app(CssProcessor::class)->process($css, $removeSelectors);

        $this->sharedAssertions($css);

        $this->assertStringContainsString('.' . CssProcessor::CSS_LIBRYO_FULL_TEXT_CLASS . ' ul', $css);
        $this->assertStringNotContainsString('/* some comment */', $css);
        $this->assertStringNotContainsString('a:visited', $css);
        $this->assertStringNotContainsString('.remove-this-whole-block', $css);
        $this->assertStringNotContainsString('.remove-this-selector table tr', $css);
        $this->assertStringContainsString('.dont-remove-this table tr', $css);

        $parser = new Parser($css);
        $cssDocument = $parser->parse();
        $foundBlock = false;
        foreach ($cssDocument->getAllDeclarationBlocks() as $block) {
            foreach ($block->getSelectors('.something-with-border-color-change') as $selector) {
                if ((string) $selector === '.' . CssProcessor::CSS_LIBRYO_FULL_TEXT_CLASS . ' .something-with-border-color-change') {
                    $foundBlock = true;

                    foreach ($block->getRules() as $rule) {
                        $this->assertStringNotContainsString('red', (string) $rule->getValue());
                        $this->assertStringNotContainsString('rgba(1,2,3,0.5)', (string) $rule->getValue());
                        $this->assertStringNotContainsString('rgb(1,2,3)', (string) $rule->getValue());
                        $this->assertStringNotContainsString('#123', (string) $rule->getValue());
                        $this->assertStringContainsString(CssProcessor::CSS_BORDER_COLOR, (string) $rule->getValue());
                    }
                }
            }
        }
        $this->assertTrue($foundBlock, 'Failed to assert that the .something-with-border-color-change block was found');
    }

    public function testProcessWithRealDocument(): void
    {
        $css = file_get_contents('./tests/Unit/Services/Arachno/Parse/test-data.css');
        $removeSelectors = [
            '/^#brexitInfo/i',
            '/^.pointInTimeView/i',
            '/^#statusWarning/i',
            '/#tools/i', // should match #tools anywhere in the selector
            '/^#whatVersion/i',
            '/^table.searchExample/i',
            '/^#coronavirus-banner/i',
            '/^#header/i',
            '/^#footer/i',
            '/^#primaryNav/i',
            '/^#contentSearch/i',
            '/^ul#secondaryNav/i',
            '/^.filesizeShow/i',
            '/^#openingOptions/i',
            '/^#breadCrumb/i',
            '/^.modWin/i',
            '/^#per.home/i',
            '/^#search/i',
            '/^.prevPagesNextNav/i',
            '/^#skipLinks/i',
            '/^#contentSearch/i',
        ];
        $css = app(CssProcessor::class)->process($css, $removeSelectors);

        $this->sharedAssertions($css);

        $this->assertStringContainsString('#leg', $css);
        $this->assertStringNotContainsString('#brexitInfo', $css);
        $this->assertStringNotContainsString('#coronavirus-banner', $css);
        $this->assertStringNotContainsString('#tools', $css);

        // $this->assertStringNotContainsString(".remove-this-selector table tr", $css);
        // $this->assertStringContainsString(".dont-remove-this table tr", $css);
    }

    public function testExtractImportLinks(): void
    {
        $css = '@import "/styles/legislation.css"; @import "/styles/primarylegislation.css"; @import "/styles/legislationOverwrites.css"; body { text-align: center;}';
        $links = app(CssProcessor::class)->extractImportLinks($css);

        $this->assertEquals('/styles/legislation.css', $links[0]);
    }

    public function testRemoveImports(): void
    {
        $css = '@import "/styles/legislation.css"; @import "/styles/primarylegislation.css"; @import "/styles/legislationOverwrites.css"; body { text-align: center;}';
        $parser = new Parser($css);
        $cssDocument = $parser->parse();
        $css = $cssDocument->render();
        $this->assertStringContainsString('@import', $css);
        app(CssProcessor::class)->removeImports($cssDocument);

        $css = $cssDocument->render();

        $this->assertStringNotContainsString('@import', $css);
    }

    private function sharedAssertions(string $css): void
    {
        foreach (app(CssProcessor::class)->getRemoveRules() as $rule) {
            $this->assertStringNotContainsString($rule . ':', $css, 'Failed to assert css does not contain: ' . $rule . ':');
        }
        // also test some expanded rules, to make sure the "background-" rule does remove them
        $this->assertStringNotContainsString('background-color:', $css);
        $this->assertStringNotContainsString('font-size:', $css);
        $this->assertStringNotContainsString('font-family:', $css);
        $this->assertStringNotContainsString('body{', $css);
    }

    private function getCss(): string
    {
        return <<<CSS
body {
    background-color:#fff;
    font-size:100.01%;
    font-family:Arial, Helvetica, sans-serif;
    margin:0;
    padding:0;
}

ul {
    list-style:none;
    margin:0;
    padding:0;
}

ol {
    list-style-type:decimal;
    margin-left:12px;
    padding:12px;
}

h1,h2,div {
    font-size:0.95em;
}

a {
    text-decoration:underline;
}

/* some comment */
a:link {
    color:#244E80;
}
/* expect this block to be removed, as it only has one rule that should get removed */
a:visited {
    color:#000;
}
.remove-this-selector table tr, .dont-remove-this table tr {
    margin-left:12px;
}
.remove-this-selector table tr, .remove-this-whole-block {
    margin-left:12px;
}

.something-with-border-color-change {
    border: 1px solid red;
    border-left: 1px solid rgba(1,2,3,0.5);
    border-right: 1px solid rgb(1,2,3);
    border-top: 1px solid #123;
    border-bottom: 1px solid red;
}

CSS;
    }
}
