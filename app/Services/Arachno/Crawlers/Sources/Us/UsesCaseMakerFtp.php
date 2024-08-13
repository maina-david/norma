<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use Aws\S3\S3Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToListContents;

/**
 * @codeCoverageIgnore
 */
trait UsesCaseMakerFtp
{
    /**
     * @return Filesystem
     */
    protected function getDisk(): Filesystem
    {
        return Storage::disk('casemaker-ftp');
    }

    public function getS3Client(): S3Client
    {
        if ($this->s3Client) {
            return $this->s3Client;
        }

        $s3Client = new S3Client([
            'region' => config('filesystems.disks.s3-images.region'),
            'version' => '2006-03-01',
            'credentials' => [
                'key' => config('filesystems.disks.s3-images.key'),
                'secret' => config('filesystems.disks.s3-images.secret'),
            ],
        ]);

        return $s3Client;
    }

    /**
     * @param string $juriDirName
     *
     * @return int
     */
    protected function getMaxYear(string $juriDirName): int
    {
        $years = collect([]);
        foreach ($this->getDirectoriesListing($juriDirName) as $year) {
            $year = Str::afterLast($year, '/');
            $years->add((int) $year);
        }

        return (int) $years->max();
    }

    /**
     * @param string $directory
     *
     * @return array<string>
     */
    protected function getDirectoriesListing(string $directory): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->getDisk();
        /** @var FtpAdapter $adapter */
        $adapter = $disk->getAdapter();

        $attempts = 0;
        $success = false;
        $dirs = [];
        while ($attempts < 4 && !$success) {
            $attempts++;
            try {
                $adapter->__destruct();
                $dirs = $disk->directories($directory);
                $success = true;
            } catch (UnableToListContents $th) {
                sleep(1);
            }
        }

        return $dirs;
    }

    /**
     * @param string $directory
     *
     * @return array<string>
     */
    protected function getFilesListing(string $directory): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->getDisk();
        /** @var FtpAdapter $adapter */
        $adapter = $disk->getAdapter();

        $attempts = 0;
        $success = false;
        $files = [];
        while ($attempts < 4 && !$success) {
            $attempts++;
            try {
                // have to force a reconnection every time, as it seems to somehow not work for every second request
                $adapter->__destruct();
                $files = $disk->files($directory);
                $success = true;
            } catch (UnableToListContents $th) {
                sleep(1);
            }
        }

        return $files;
    }

    /**
     * @param string $directory
     *
     * @return bool
     */
    protected function checkIfDirectoryExists(string $directory): bool
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->getDisk();
        /** @var FtpAdapter $adapter */
        $adapter = $disk->getAdapter();

        $attempts = 0;
        $success = false;
        $exists = false;
        while ($attempts < 4 && !$success) {
            $attempts++;
            try {
                // have to force a reconnection every time, as it seems to somehow not work for every second request
                $adapter->__destruct();
                $exists = $disk->directoryExists($directory);
                $success = true;
            } catch (UnableToCheckExistence $e) {
                sleep(1);
            }
        }

        return $exists;
    }
}
