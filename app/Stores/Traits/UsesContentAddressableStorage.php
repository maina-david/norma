<?php

namespace App\Stores\Traits;

use Exception;
use Storage;

trait UsesContentAddressableStorage
{
    /**
     * @param string $content
     *
     * @throws Exception
     *
     * @return string
     */
    public function put(string $content): string
    {
        $hash = sha1($content);
        $compressed = gzencode($content, 9);
        if ($compressed === false) {
            // @codeCoverageIgnoreStart
            throw new Exception('GZIP compression failed on content');
            // @codeCoverageIgnoreEnd
        }
        $path = $this->getPath($hash);
        Storage::disk($this->getDiskName())
            ->put($path, $compressed);

        return $path;
    }

    /**
     * @param string $path
     *
     * @throws Exception
     *
     * @return string|null
     */
    public function get(string $path): ?string
    {
        $content = Storage::disk($this->getDiskName())
            ->get($path);
        if (is_null($content)) {
            return null;
        }

        $decoded = gzdecode($content);
        if ($decoded === false) {
            // @codeCoverageIgnoreStart
            throw new Exception('Unable to decode content for ' . $path);
            // @codeCoverageIgnoreEnd
        }

        /** @var string */
        return $decoded;
    }

    /**
     * @param string $hash
     *
     * @return string
     */
    public function getPath(string $hash): string
    {
        $path = static::PATH_PREFIX;
        for ($i = 0; $i < 6; $i++) {
            $path .= '/' . $hash[$i];
        }

        $path .= '/' . $hash;

        return $path;
    }
}
