<?php

namespace App\Stores\Corpus;

use App\Enums\Storage\FileType;
use App\Models\Corpus\ContentResource;
use App\Services\Arachno\Parse\PdfExtractor;
use App\Services\Html\HtmlToText;
use App\Stores\Traits\UsesContentAddressableStorage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class ContentResourceStore
{
    use UsesContentAddressableStorage;

    public const PATH_PREFIX = '/content';

    public function __construct(protected PdfExtractor $pdfExtractor, protected HtmlToText $htmlToText)
    {
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getLink(string $path): string
    {
        return $path;
    }

    /**
     * @param ContentResource $resource
     *
     * @return string
     */
    public function getLinkForResource(ContentResource $resource): string
    {
        return $this->getLink($resource->path ?? '');
    }

    /**
     * @param string $content
     * @param string $mimeType
     *
     * @return ContentResource
     */
    public function storeResource(string $content, string $mimeType): ContentResource
    {
        $hash = ContentResource::hashUid($content);
        $uid = ContentResource::hashForDB($content);

        /** @var ContentResource|null $resource */
        $resource = ContentResource::where('uid', $uid)->first();
        if (is_null($resource)) {
            $resource = new ContentResource([
                'uid' => $uid,
                'content_hash' => $hash,
                'file_name' => $hash,
                'mime_type' => $mimeType,
                'size' => strlen($content),
                'path' => $this->getPath($hash),
            ]);
            try {
                // still managed to get duplicate entry error for UID in production,
                // maybe because two separate jobs worked on adding the same image
                $resource->save();
                $this->put($content);
                // @codeCoverageIgnoreStart
            } catch (QueryException $e) {
                /** @var ContentResource $resource */
                $resource = ContentResource::where('uid', $uid)->first();
            }
            // @codeCoverageIgnoreEnd

            if ($resource->id) {
                $this->storePlainText($resource, $content, $mimeType);
            }
        }

        /** @var ContentResource */
        return $resource;
    }

    /**
     * @param ContentResource $resource
     *
     * @return bool|null
     */
    public function deleteResource(ContentResource $resource): ?bool
    {
        $path = $this->getPath($resource->content_hash ?? '');
        Storage::disk($this->getDiskName())
            ->delete($path);

        return $resource->delete();
    }

    public function getDiskName(): string
    {
        return config('filesystems.filetypes.' . FileType::content()->value . '.disk');
    }

    public function storePlainText(ContentResource $resource, string $content, string $mimeType): void
    {
        $text = '';
        switch ($mimeType) {
            case 'application/pdf':
                $text = $this->pdfExtractor->getText($content);
                break;
            case 'application/xhtml+xml':
            case 'text/html':
                $text = $this->htmlToText->convert($content);
                break;
        }

        if ($text) {
            $textResource = $this->storeResource($text, 'text/plain');
            $resource->update(['text_content_resource_id' => $textResource->id]);
        }
    }
}
