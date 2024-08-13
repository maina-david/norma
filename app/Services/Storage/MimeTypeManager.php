<?php

namespace App\Services\Storage;

use App\Models\Arachno\ContentCache;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class MimeTypeManager
{
    /**
     * @return array<string>
     */
    public function getImageMimeTypes(): array
    {
        $mimeTypes = new MimeTypes();
        $types = $mimeTypes->getMimeTypes('jpg');
        $types = array_merge($types, $mimeTypes->getMimeTypes('gif'));
        $types = array_merge($types, $mimeTypes->getMimeTypes('png'));
        $types = array_merge($types, $mimeTypes->getMimeTypes('webp'));

        return $types;
    }

    /**
     * @return array<string>
     */
    public function getAcceptedProfileImageMimeTypes(): array
    {
        $mimeTypes = new MimeTypes();
        $types = $mimeTypes->getMimeTypes('jpg');

        return $types;
    }

    /**
     * @return array<string>
     */
    public function getAcceptedUploads(): array
    {
        $mimeTypes = new MimeTypes();
        $extensions = [];
        foreach (config('filesystems.supported.uploads') as $type) {
            $extensions = array_merge($extensions, $mimeTypes->getExtensions($type));
        }

        return array_merge($extensions, config('filesystems.supported.uploads'));
    }

    /**
     * Get the mime types that can be uploaded.
     *
     * @return array<string>
     */
    public function getAcceptedUploadMimes(): array
    {
        return config('filesystems.supported.uploads');
    }

    /**
     * Get the mime types that can be imported via excel.
     *
     * @return array<string>
     */
    public function getAcceptedExcelMimes(): array
    {
        $mimeTypes = new MimeTypes();

        return array_merge(
            $mimeTypes->getMimeTypes('xls'),
            $mimeTypes->getMimeTypes('xlsx'),
        );
    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    public function isImage(string $contentType): bool
    {
        return in_array($contentType, $this->getImageMimeTypes());
    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    public function isPdf(string $contentType): bool
    {
        $mimeTypes = new MimeTypes();

        return in_array($contentType, $mimeTypes->getMimeTypes('pdf'));
    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    public function isCss(string $contentType): bool
    {
        $mimeTypes = new MimeTypes();

        return in_array($contentType, $mimeTypes->getMimeTypes('css'));
    }

    public function isDocx(string $contentType): bool
    {
        $mimeTypes = new MimeTypes();

        return in_array($contentType, $mimeTypes->getMimeTypes('docx'));
    }

    public function isHtml(string $contentType): bool
    {
        $mimeTypes = new MimeTypes();

        return in_array($contentType, $mimeTypes->getMimeTypes('html'));
    }

    public function isJson(string $contentType): bool
    {
        $mimeTypes = new MimeTypes();

        return in_array($contentType, $mimeTypes->getMimeTypes('json'));
    }

    // public function isXml(string $contentType): bool
    // {
    //     $mimeTypes = new MimeTypes();

    //     return in_array($contentType, $mimeTypes->getMimeTypes('xml'));
    // }

    /**
     * @param array<string, mixed> $headers
     *
     * @return string|null
     */
    public function extractFromHeaders(array $headers): ?string
    {
        $key = isset($headers['content-type']) ? 'content-type' : 'Content-Type';

        return explode(';', $headers[$key][0] ?? '')[0] ?? null;
    }

    /**
     * @param ContentCache $contentCache
     *
     * @return string|null
     */
    public function extractFromContentCache(ContentCache $contentCache): ?string
    {
        $headers = $contentCache->response_headers ?? [];
        $mimeType = $this->extractFromHeaders($headers);

        if (!$mimeType) {
            $mimeTypes = new MimeTypes();
            $ext = Str::afterLast($contentCache->url ?? '', '.');
            if ($ext) {
                $mimeType = $mimeTypes->getMimeTypes($ext)[0] ?? '';
            }
        }

        return $mimeType;
    }
}
