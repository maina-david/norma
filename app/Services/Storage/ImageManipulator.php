<?php

namespace App\Services\Storage;

use Illuminate\Filesystem\FilesystemManager;
use Intervention\Image\Image;

class ImageManipulator
{
    /** @var FilesystemManager */
    protected $filesystem;

    /**
     * @param FilesystemManager $filesystem
     **/
    public function __construct(FilesystemManager $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param Image $image
     *
     * @return Image
     **/
    public function resizeMedium(Image $image)
    {
        return $this->resize($image, config('image.sizes.medium'));
    }

    /**
     * @param Image $image
     *
     * @return Image
     **/
    public function resizeSmall(Image $image)
    {
        return $this->resize($image, config('image.sizes.small'));
    }

    /**
     * @param Image $image
     * @param int   $size
     *
     * @return Image
     **/
    private function resize(Image $image, $size)
    {
        if ($image->width() >= $image->height()) {
            $width = $size;
            $height = null;
        } else {
            // @codeCoverageIgnoreStart
            $width = null;
            $height = $size;
            // @codeCoverageIgnoreEnd
        }

        return $image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }

    /**
     * @param Image  $image
     * @param string $fileName
     * @param string $baseDirectory base directory to store image under - remember trailing /
     *
     * @return string
     **/
    public function storeImage(
        Image $image,
        string $fileName,
        string $baseDirectory = ''
    ): string {
        $image->getCore()->stripImage();

        $directory = $baseDirectory . config('filesystems.images.path') . '/' . strtolower(substr($fileName, 0, 1));
        // make a directory if it doesn't exist
        $this->filesystem->makeDirectory($directory);
        $this->filesystem->put($directory . '/original/' . $fileName, (string) $image->encode());
        $this->resizeMedium($image);
        $this->filesystem->put($directory . '/medium/' . $fileName, (string) $image->encode());
        $this->resizeSmall($image);
        $this->filesystem->put($directory . '/small/' . $fileName, (string) $image->encode());

        return $directory . '/original/' . $fileName;
    }
}
