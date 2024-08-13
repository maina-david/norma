<?php

namespace App\Services\Translation;

use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslator implements TranslatorServiceInterface
{
    /** @var TranslateClient */
    protected $client;

    public function __construct()
    {
        $this->client = new TranslateClient([
            'projectId' => config('services.google_cloud.project_id'),
            'keyFile' => config('services.google_cloud.key_file'),
            'suppressKeyFileNotice' => true,
        ]);
    }

    public function getChunkLimit(): int
    {
        // @codeCoverageIgnoreStart
        return 5000;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    public function translate(
        string $translationText,
        string $targetLanguage,
        string $sourceLanguage
    ): string {
        if (!config('translation.enabled')) {
            return $translationText;
        }

        $result = $this->client->translate($translationText, [
            'target' => $targetLanguage,
            'source' => $sourceLanguage,
        ]);

        return $result['text'] ?? $translationText;
    }

    // /**
    //  * @param array  $translationText
    //  * @param string $targetLanguage
    //  * @param string $sourceLanguage
    //  **/
    // public function translateBatch(
    //     array $translationText,
    //     string $targetLanguage,
    //     string $sourceLanguage
    // ): array {
    //     if (!config('translation.enabled')) {
    //         return $translationText;
    //     }

    //     $results = $this->client->translateBatch($translationText, [
    //         'target' => $targetLanguage,
    //         'source' => $sourceLanguage,
    //     ]);

    //     $out = [];
    //     foreach ($results as $res) {
    //         $out[] = $res['text'];
    //     }

    //     return $out;
    // }

    // /**
    //  * {@inheritDoc}
    //  **/
    // public function languages(): array
    // {
    //     return $this->client->localizedLanguages(['target' => 'en']);
    // }
}
