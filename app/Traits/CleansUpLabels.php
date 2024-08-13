<?php

namespace App\Traits;

trait CleansUpLabels
{
    /**
     * Remove the unwanted characters from the provided text.
     *
     * @param string $text
     *
     * @return string
     */
    public static function cleanText(string $text): string
    {
        $text = htmlentities($text);
        /** @var string $text */
        $text = preg_replace('/(↵|\t|\n|\r|\r\n)+/m', ' ', $text);
        /** @var string $text */
        $text = preg_replace('/(&nbsp;|\s)+/m', ' ', $text);

        $text = trim($text);

        $text = html_entity_decode($text);
        /** @var string $text */
        $text = preg_replace('/(&nbsp;|\s)+/m', ' ', $text);

        $text = trim($text);
        /** @var string $text */
        $text = preg_replace('/[.()[\],;\-*|<>\'"]/m', '', $text);

        /** @var string $text */
        $text = str_replace('section', '', $text);
        /** @var string $text */
        $text = str_replace('sec', '', $text);

        $text = trim($text);

        /** @var string $text */
        $text = preg_replace('/\s/m', '', $text);

        return trim($text);
    }
}
