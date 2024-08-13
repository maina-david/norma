<?php

namespace App\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\PdfProperty;
use App\Services\Html\HtmlToText;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Throwable;

class PdfExtractor
{
    public function __construct(protected HtmlToText $htmlToText)
    {
    }

    public function getProperty(PdfProperty $prop, string $pdfContent): string
    {
        $info = new PdfInfo();

        $details = $info->getProperties($pdfContent);

        return match ($prop) {
            PdfProperty::TITLE => $details['Title'] ?? '',
            PdfProperty::SUBJECT => $details['Subject'] ?? '',
            PdfProperty::KEYWORDS => $details['Keywords'] ?? '',
            PdfProperty::CREATOR => $details['Creator'] ?? '',
            PdfProperty::PRODUCER => $details['Producer'] ?? '',
            PdfProperty::CREATION_DATE => $details['CreationDate'] ?? '',
        };
    }

    public function getText(string $pdfContent): string
    {
        $text = $this->extractText($pdfContent);
        // if no text, it might need to be OCR'ed
        if (trim($text) === '') {
            $text = $this->getTextWithOCR($pdfContent);
        }

        return $text;
    }

    /**
     * @param string $pdfContent
     *
     * @return string
     */
    private function extractText(string $pdfContent): string
    {
        if (config('services.tika.enabled')) {
            // @codeCoverageIgnoreStart
            return $this->getTextUsingTika($pdfContent);
            // @codeCoverageIgnoreEnd
        }

        return $this->getTextUsingParser($pdfContent);
    }

    protected function getTextUsingParser(string $pdfContent): string
    {
        $config = new Config();
        $config->setDecodeMemoryLimit(8 * 1024);
        $parser = new Parser([], $config);

        try {
            $pdf = $parser->parseContent($pdfContent);
            $text = $pdf->getText();
            // @codeCoverageIgnoreStart
        } catch (Throwable $th) {
            $text = '';
        }

        // @codeCoverageIgnoreEnd
        return $text;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $pdfContent
     *
     * @return string
     */
    protected function getTextUsingTika(string $pdfContent): string
    {
        $host = config('services.tika.host');
        try {
            $response = Http::accept('text/html')
                ->withBody($pdfContent, 'application/pdf')
                ->put($host . '/tika');
            // @codeCoverageIgnoreStart
        } catch (Throwable $th) {
            return $this->getTextUsingParser($pdfContent);
        }
        // @codeCoverageIgnoreEnd

        $html = (string) $response;

        return $this->htmlToText->convert($html);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $pdfContent
     *
     * @return string
     */
    public function getTextWithOCR(string $pdfContent): string
    {
        // still to implement ocrmypdf service...
        $newPdfContent = '';

        $this->extractText($newPdfContent);

        return '';
    }
}
