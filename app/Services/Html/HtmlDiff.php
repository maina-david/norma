<?php

namespace App\Services\Html;

use cogpowered\FineDiff\Diff;

class HtmlDiff
{
    public function __construct(protected HtmlToText $htmlToText)
    {
    }

    public function compare(string $string1, string $string2): string
    {
        $diff = new Diff();

        $string1 = $this->htmlToText->convert($string1);
        $string2 = $this->htmlToText->convert($string2);

        return $diff->render($string1, $string2);
    }
}
