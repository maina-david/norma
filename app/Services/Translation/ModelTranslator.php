<?php

namespace App\Services\Translation;

use App\Contracts\Translation\HasTranslations;
use App\Models\Corpus\Reference;
use App\Models\Lookups\Translation;
use App\Models\Requirements\Summary;
use App\Support\Languages;
use DomainException;

class ModelTranslator
{
    /**
     * @param TranslatorServiceInterface $translator
     */
    public function __construct(
        protected TranslatorServiceInterface $translator,
    ) {
    }

    // /**
    //  * Translates the given register item into English, updates the translation if it already exists
    //  *
    //  * @param Reference $registerItem
    //  *
    //  * @return string
    //  **/
    // public function updateRegisterItemEnglishTranslation(Reference $registerItem)
    // {
    //     $englishTrans = $this->getTranslationOrTranslate($registerItem, 'en');
    //     $englishTrans->update(['translated_text' => $englishTrans->translated_text]);
    // }

    /**
     * Translates the given summary into the given language. We store the
     * translations in the DB, so if a translation into that language already
     * exists, return that.
     *
     * @param Summary $summary
     * @param string  $targetLanguage
     *
     * @return string
     **/
    public function translateSummary(
        Summary $summary,
        string $targetLanguage
    ): string {
        $translation = $this->getTranslationOrTranslate($summary, $targetLanguage);

        return $translation->translated_text;
    }

    /**
     * Translates the given Reference into the given language. We store the
     * translations in the DB, so if a translation into that language already
     * exists, return that.
     *
     * @param Reference $registerItem
     * @param string    $targetLanguage
     *
     * @return string
     **/
    public function translateReference(
        Reference $registerItem,
        string $targetLanguage = 'en'
    ): string {
        $translation = $this->getTranslationOrTranslate($registerItem, $targetLanguage);

        return $translation->translated_text;
    }

    // /**
    //  * Updates the given summaries existing translations
    //  *
    //  * @param Summary $summary
    //  **/
    // public function updateSummaryTranslations(Summary $summary)
    // {
    //     //summaries are always English (for now and the foreseeable future)
    //     $sourceLanguage = 'en';
    //     foreach ($summary->translations as $translation) {
    //         $translatedText = $this->translator->translate(
    //             $summary->summary_body,
    //             $translation->target_language,
    //             $sourceLanguage
    //         );
    //         $translation->update(['translated_text' => $translatedText]);
    //     }
    // }

    /**
     * @param HasTranslations $model
     * @param string          $targetLanguage
     *
     * @return Translation
     **/
    protected function getTranslationOrTranslate(
        HasTranslations $model,
        string $targetLanguage
    ): Translation {
        //        if ($translation = $model->translations->firstWhere('target_language', $targetLanguage)) {
        //            // @codeCoverageIgnoreStart
        //            /** @var Translation */
        //            return $translation;
        //            // @codeCoverageIgnoreEnd
        //        }

        return $this->translateModel($model, $targetLanguage);
    }

    /**
     * @param HasTranslations $model
     * @param string          $targetLanguage
     *
     * @throws DomainException
     *
     * @return Translation
     **/
    protected function translateModel(
        HasTranslations $model,
        string $targetLanguage
    ): Translation {
        $textToTranslate = $model instanceof Summary
            ? $model->summary_body
            // @phpstan-ignore-next-line
            : $model->htmlContent?->cached_content;
        $sourceLanguage = $model instanceof Summary
            ? 'en'
            // @phpstan-ignore-next-line
            : $this->toAlpha2($model->work->language_code);
        if ($sourceLanguage === $targetLanguage) {
            throw new DomainException("Source and Target language can't be the same");
        }

        $translatedText = $this->translate(
            $textToTranslate,
            $sourceLanguage,
            $targetLanguage
        );

        return new Translation([
            'target_language' => $targetLanguage,
            'source_language' => $sourceLanguage,
            'summary_id' => $model instanceof Summary ? $model->reference_id : null,
            'register_item_id' => $model instanceof Reference ? $model->id : null,
            'translated_text' => $translatedText,
        ]);
    }

    /**
     * Get the translation.
     *
     * @param string $text
     * @param string $sourceLanguage
     * @param string $targetLanguage
     *
     * @return string
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): string
    {
        return $this->translator->translate($text, $targetLanguage, $sourceLanguage);
    }

    /**
     * Converts alpha3 (ISO 639-2) to alpha2 (ISO 639-1) language code.
     *
     * @param string $languageCode
     **/
    public function toAlpha2(string $languageCode): string
    {
        return Languages::$alpha3To2[$languageCode] ?? $languageCode;
    }
}
