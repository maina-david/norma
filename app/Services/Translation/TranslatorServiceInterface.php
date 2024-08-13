<?php

namespace App\Services\Translation;

interface TranslatorServiceInterface
{
    /**
     * Translate the given string to the given language.
     *
     * @param string $translationText
     * @param string $targetLanguage
     * @param string $sourceLanguage
     *
     * @return string
     **/
    public function translate(
        string $translationText,
        string $targetLanguage,
        string $sourceLanguage
    ): string;

    /**
     * Returns the character limit that can be sent per request.
     *
     * @return int
     **/
    public function getChunkLimit(): int;
}
