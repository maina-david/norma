<?php

namespace App\Services\Storage;

class FileHelper
{
    /**
     * Get a human readable string for the given size in bytes.
     *
     * @param int $bytes
     * @param int $decimals
     *
     * @return string
     */
    public function getHumanReadableSize(int $bytes, int $decimals = 1): string
    {
        if ($bytes == 0) {
            // @codeCoverageIgnoreStart
            return '0.00B';
            // @codeCoverageIgnoreEnd
        }

        $s = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $e = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $e), $decimals) . $s[$e];
    }
}
