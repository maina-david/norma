<?php

namespace App\Services\Arachno\Ocr;

use App\Services\Arachno\OcrMyPdf;

class PdfOcr
{
    /**
     * @param OcrMyPdf $ocrMyPdf
     */
    public function __construct(protected OcrMyPdf $ocrMyPdf)
    {
    }

    /**
     * @param string              $content
     * @param array<string,mixed> $ocrOptions
     *
     * @return string
     */
    public function ocr(
        string $content,
        array $ocrOptions,
    ): string {
        $provider = $ocrOptions['provider'] ?? 'ocr_my_pdf';

        return match ($provider) {
            // @codeCoverageIgnoreStart
            'ocr_space' => $this->ocrWithOcrSpace($content, $ocrOptions),
            // @codeCoverageIgnoreEnd
            default => $this->ocrWithOcrMyPdf($content, $ocrOptions),
        };
    }

    /**
     * @param string              $content
     * @param string              $options
     * @param array<string,mixed> $options
     *
     * @return string
     */
    protected function ocrWithOcrMyPdf(string $content, array $options = []): string
    {
        $languages = $options['languages'] ?? ['eng'];
        $forcOcr = $options['force-ocr'] ?? true;

        $forcOcrStr = $forcOcr ? '--force-ocr' : '';
        $languages = implode('+', $languages);
        $optionsStr = "-l {$languages} {$forcOcrStr}";

        return $this->ocrMyPdf->ocr($content, $optionsStr);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string              $content
     * @param string              $options
     * @param array<string,mixed> $options
     *
     * @return string
     */
    protected function ocrWithOcrSpace(string $content, array $options): string
    {
        // TODO: implement https://ocr.space/OCRAPI if required
        return '';
    }
}
