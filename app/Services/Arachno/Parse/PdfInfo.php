<?php

namespace App\Services\Arachno\Parse;

use Carbon\Carbon;
use Illuminate\Support\Facades\Process;
use Throwable;

class PdfInfo
{
    /**
     * Get the document properties.
     *
     * @param string $content
     *
     * @return array<string, string>
     */
    public function getProperties(string $content): array
    {
        $meta = [];
        $result = $this->runProcess($content);

        if (!empty($result)) {
            $meta = $this->extractMeta($result);
        }

        return $meta;
    }

    /**
     * Run the process and get the meta.
     *
     * @param string $content
     *
     * @return string
     */
    protected function runProcess(string $content): string
    {
        $file = tmpfile();
        $result = '';

        if ($file) {
            fwrite($file, $content);

            /** @var string $binary */
            $binary = config('services.pdfinfo.binary');

            /** @var array<string, mixed> $meta */
            $meta = stream_get_meta_data($file);

            $result = Process::run([$binary, $meta['uri']])->output();

            fclose($file);
        }

        return $result;
    }

    /**
     * Extract the meta from the generated xml.
     *
     * @param string $result
     *
     * @return array<string, string>
     */
    protected function extractMeta(string $result): array
    {
        /** @var array<string, string> */
        return collect(explode("\n", trim($result)))
            ->mapWithKeys(function ($item) {
                $row = explode(':', $item);

                $key = array_shift($row);
                $value = implode(':', $row);

                if (strlen($value) > 8 && str_contains($key, 'Date')) {
                    try {
                        $parsed = Carbon::parse($value)->toIso8601String();
                        $value = $parsed;
                        // @codeCoverageIgnoreStart
                    } catch (Throwable) {
                    }
                    // @codeCoverageIgnoreEnd
                }

                return [trim($key) => trim($value)];
            })
            ->all();
    }
}
