<?php

namespace App\Exports\Traits;

use PhpOffice\PhpSpreadsheet\Helper\Html;

trait CleansHtmlForExcel
{
    /**
     * Parse the html content to form a rich text object for insertion into an excel cell.
     *
     * @param string $content
     *
     * @return string
     */
    protected function parseHTML(string $content): string
    {
        $wizard = new Html();

        $content = str_replace('<p>&nbsp;</p>', "\n\n", $content);
        $content = str_replace('<br>', "\n\n", $content);
        /** @var string */
        $content = preg_replace('/<\s*([a-z][a-z0-9\-]*)\s.*?>/i', '<$1>', $content); // replace all attributes
        $content = $wizard->toRichTextObject($content)->__toString();
        // $content = preg_replace('/[\x80-\xFF]/', '', $content);

        return mb_convert_encoding($content, 'UTF-8');
    }
}
