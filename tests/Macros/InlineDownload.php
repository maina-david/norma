<?php

namespace Tests\Macros;

use Illuminate\Http\Response;
use Illuminate\Testing\Assert as PHPUnit;

class InlineDownload
{
    /**
     * Macro to assert that the attachment is inline.
     *
     * @return void
     */
    public static function apply(): void
    {
        Response::macro('assertInlineDownload', function ($filename = null) {
            /** @phpstan-ignore-next-line */
            $contentDisposition = explode(';', $this->headers->get('content-disposition') ?? '');

            if (trim($contentDisposition[0]) !== 'inline') {
                PHPUnit::fail(
                    'Response does not offer a file download.' . PHP_EOL .
                    'Disposition [' . trim($contentDisposition[0]) . '] found in header, [attachment] expected.'
                );
            }

            if (!is_null($filename)) {
                if (
                    isset($contentDisposition[1])
                    && trim(explode('=', $contentDisposition[1])[0]) !== 'filename'
                ) {
                    PHPUnit::fail(
                        sprintf(
                            'Unsupported Content-Disposition header.%s[%s] found in header, [filename] expected.',
                            PHP_EOL,
                            trim(explode('=', $contentDisposition[1])[0])
                        )
                    );
                }

                $message = "Expected file [{$filename}] is not present in Content-Disposition header.";

                if (!isset($contentDisposition[1])) {
                    PHPUnit::fail($message);
                } else {
                    PHPUnit::assertSame(
                        $filename,
                        isset(explode('=', $contentDisposition[1])[1])
                            ? trim(explode('=', $contentDisposition[1])[1])
                            : '',
                        $message
                    );

                    /* @phpstan-ignore-next-line */
                    return $this;
                }
            } else {
                PHPUnit::assertTrue(true);

                /* @phpstan-ignore-next-line */
                return $this;
            }
        });
    }
}
