<?php

namespace App\Traits;

use App\Enums\Storage\FileType;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait InteractsWithStorage
{
    /**
     * Get the path to the item in storage.
     *
     * @return string
     */
    abstract protected function filePath(): string;

    /**
     * Get the name of the file.
     *
     * @return string
     */
    abstract protected function fileName(): string;

    /**
     * Get the mime type of the file.
     *
     * @return string
     */
    abstract protected function fileMimeType(): string;

    /**
     * Handle the changes required after the file has been saved in storage.
     *
     * @param UploadedFile $file
     * @param string       $path
     *
     * @return void
     */
    abstract protected function fileSaved(UploadedFile $file, string $path): void;

    /**
     * Return the FileType of this file.
     *
     * @return FileType
     */
    abstract protected function getFileType(): FileType;

    /**
     * Get the extra headers to send back when downloading/streaming.
     *
     * @return array<string, mixed>
     */
    protected function fileHeaders(): array
    {
        return [];
    }

    /**
     * Generate a response of the file, either inline or download.
     *
     * @param string $disposition
     * @param bool   $attachExtension
     *
     * @return Response
     */
    public function streamFile(string $disposition = 'inline', bool $attachExtension = false): Response
    {
        set_time_limit(600);

        if (Storage::missing($this->filePath())) {
            // @codeCoverageIgnoreStart
            abort(404);
            // @codeCoverageIgnoreEnd
        }

        $filename = $this->fileName() . ($attachExtension ? '.' . $this->extension : '');
        $filename = str_replace(',', ' ', $filename);

        $response = (new Response(Storage::get($this->filePath()), 200))
            ->header('Content-Type', $this->fileMimeType())
            ->header('Content-Disposition', "{$disposition}; filename={$filename}");

        collect($this->fileHeaders())->each(fn ($value, $key) => $response->header($key, $value));

        return $response;
    }

    /**
     * Generate a response of the image file, either inline or download.
     * $size can be medium, small or original.
     *
     * @param string $size
     * @param string $disposition
     * @param bool   $attachExtension
     *
     * @return Response
     */
    public function streamImageFile(string $size, string $disposition = 'inline', bool $attachExtension = false): Response
    {
        set_time_limit(600);

        $path = str_replace('/original/', '/' . $size . '/', $this->filePath());

        if (Storage::missing($path)) {
            // @codeCoverageIgnoreStart
            abort(404);
            // @codeCoverageIgnoreEnd
        }

        $filename = $this->fileName() . ($attachExtension ? '.' . $this->extension : '');

        return (new Response(Storage::get($path), 200))
            ->header('Content-Type', $this->fileMimeType())
            ->header('Content-Disposition', "{$disposition}; filename={$filename}");
    }

    /**
     * Download the file from storage.
     *
     * @param bool $attachExtension
     *
     * @return Response
     */
    public function download(bool $attachExtension = false): Response
    {
        return $this->streamFile('attachment', $attachExtension);
    }

    /**
     * Download the file from storage.
     *
     * @return Response
     */
    public function stream(): Response
    {
        return $this->streamFile();
    }

    /**
     * Get the path to the directory that all files should be stored.
     *
     * @throws Exception
     *
     * @return string
     */
    protected function storageDirectory(): string
    {
        $type = $this->getFileType();
        if ($directory = config("filesystems.filetypes.{$type->value}.path")) {
            return $directory;
        }

        // @codeCoverageIgnoreStart
        throw new Exception('Update the filesystems.php file and add the attachment type to the attachments array.');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Store the uploaded file and return an updated instance of the implementer
     * of this trait.
     *
     * @param UploadedFile $file
     *
     * @throws Exception
     *
     * @return self
     */
    public function saveFile(UploadedFile $file): self
    {
        $path = Storage::putFile($this->storageDirectory(), $file);

        if ($path) {
            $this->fileSaved($file, $path);
        }

        return $this;
    }

    /**
     * Delete the file.
     *
     * @return self
     */
    public function deleteFile(): self
    {
        if (Storage::exists($this->filePath())) {
            Storage::delete($this->filePath());
        }

        return $this;
    }
}
