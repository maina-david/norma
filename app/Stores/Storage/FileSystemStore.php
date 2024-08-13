<?php

namespace App\Stores\Storage;

use App\Services\Storage\ImageManipulator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileSystemStore
{
    public function __construct(protected ImageManipulator $imageManipulator)
    {
    }

    /**
     * @param UploadedFile $file
     * @param string       $fileName
     * @param string       $directory
     *
     * @return string
     */
    public function store(UploadedFile $file, string $fileName, string $directory): string
    {
        if (in_array($file->getClientMimeType(), config('filesystems.supported.images'))) {
            $image = Image::make($file);

            $path = $this->imageManipulator->storeImage($image, $fileName, $directory . DIRECTORY_SEPARATOR);

            return $path;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $fileName;
        $file->storeAs($directory . DIRECTORY_SEPARATOR, $fileName);

        return $path;
    }

    /**
     * Delete a file from the file system. If an image, delete the variations as well.
     *
     * @param string $path
     * @param string $mimeType
     *
     * @return void
     */
    public function destroyFileInStorage(string $path, string $mimeType): void
    {
        if (in_array($mimeType, config('filesystems.supported.images'))) {
            Storage::delete(str_replace('/original/', '/small/', $path));
            Storage::delete(str_replace('/original/', '/medium/', $path));
        }

        Storage::delete($path);
    }
}
