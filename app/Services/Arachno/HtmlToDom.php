<?php

namespace App\Services\Arachno;

use DOMDocument;

class HtmlToDom
{
    // public function convert(string $html): DOMDocument
    // {
    //     libxml_use_internal_errors(true);
    //     mb_detect_order('UTF-8,ISO-8859-1,ASCII,windows-1252,iso-8859-15');
    //     $encoding = mb_detect_encoding($html);
    //     $doc = new DOMDocument('1.0', 'UTF-8');
    //     $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $encoding ?: null));
    //     libxml_use_internal_errors(false);

    //     return $doc;
    // }
}
