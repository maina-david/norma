<?php

namespace App\Services\Language;

use App\Support\Languages;
use LanguageDetection\Language;

class LanguageDetector
{
    public const DEFAULT_LANGUAGE = 'eng';

    /**
     * @param string $text
     *
     * @return string|null
     */
    public function detect(string $text): ?string
    {
        $ld = new Language();
        $result = $ld->detect($text)->close();

        $result = array_keys($result);
        if (empty($result)) {
            return null;
        }

        $langs = array_flip(Languages::$alpha3To2);

        return $langs[$result[0] ?? 'en'] ?? static::DEFAULT_LANGUAGE;
    }
}
