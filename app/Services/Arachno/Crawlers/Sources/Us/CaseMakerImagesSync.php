<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Jobs\Arachno\Sources\Us\CasemakerSyncImagesFromZipFile;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\Transfer;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Iterator;
use League\Flysystem\Ftp\FtpAdapter;
use ZipArchive;

/**
 * @codeCoverageIgnore
 */
class CaseMakerImagesSync
{
    use UsesCaseMakerFtp;

    protected ?S3Client $s3Client = null;

    protected ?Transfer $s3Transfer = null;

    /**
     * @param string|Iterator<mixed> $source
     * @param string                 $destination
     * @param array<string, mixed>   $options
     *
     * @return Transfer
     */
    public function getS3Transfer(string|Iterator $source, string $destination, array $options = []): Transfer
    {
        if ($this->s3Transfer) {
            return $this->s3Transfer;
        }

        $s3Transfer = new Transfer($this->getS3Client(), $source, $destination, $options);

        return $s3Transfer;
    }

    /**
     * @param string $dir
     *
     * @return void
     */
    public function sync(string $dir): void
    {
        $baseDir = strtoupper($dir);
        $this->browseDir($baseDir);
    }

    private function browseDir(string $dir): void
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->getDisk();

        $adapter = $disk->getAdapter();
        /** @var FtpAdapter $adapter */

        // have to force a reconnection every time, as it seems to somehow not work for every second request
        $adapter->__destruct();
        foreach ($disk->directories($dir) as $year) {
            $currentYear = now()->format('Y');
            if (!str_contains($year, $currentYear)) {
                if (app()->environment('local')) {
                    echo 'Skipping ' . $year . PHP_EOL;
                }
                continue;
            }

            /** @var string $year */
            $adapter->__destruct();
            foreach ($disk->files($year) as $file) {
                if (Str::endsWith($file, '.zip')) {
                    CasemakerSyncImagesFromZipFile::dispatch($file)
                        ->onQueue('long-running');
                }
            }

            $this->browseDir($year);
        }
    }

    public function extractAndSyncFile(string $filePath): void
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->getDisk();
        $adapter = $disk->getAdapter();
        /** @var FtpAdapter $adapter */
        $localDisk = Storage::disk('local');
        $adapter->__destruct();
        /** @var string */
        $zipFile = $disk->get($filePath);

        $tmp = config('filesystems.paths.temp');
        $tmpDir = $tmp . DIRECTORY_SEPARATOR . Str::random();
        $tmpDirLocal = config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . $tmpDir . DIRECTORY_SEPARATOR;
        $localDisk->makeDirectory($tmpDir);

        // store the zip file to local disk
        $fileName = Str::afterLast($filePath, '/');
        $zipFileLocal = $tmp . DIRECTORY_SEPARATOR . $fileName;
        $localDisk->put($zipFileLocal, $zipFile);

        if (app()->environment('local')) {
            echo config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . $zipFileLocal . PHP_EOL;
        }

        // attempt to extract the file to a temporary directory
        $zip = new ZipArchive();
        if ($zip->open(config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . $zipFileLocal) === true) {
            $zip->extractTo($tmpDirLocal);
            $zip->close();
        } else {
            if (app()->environment('local')) {
                echo 'Error extracting zip file: ' . config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . $zipFileLocal . PHP_EOL;
            }
        }

        $parts = explode('_', $fileName);
        $year = $parts[2];
        $s3Dir = 's3://' . config('filesystems.disks.s3-images.bucket') . '/cmdata/' . $parts[0] . '/' . $parts[1] . '/';

        if (app()->environment('local')) {
            echo $s3Dir . PHP_EOL;
        }
        // transfer to S3
        $trans = $this->getS3Transfer($tmpDirLocal . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR, $s3Dir);
        try {
            $trans->transfer();
            if (app()->environment('local')) {
                echo 'Transfer complete' . PHP_EOL;
            }
        } catch (S3Exception $e) {
            if (app()->environment('local')) {
                echo 'Transfer failed' . PHP_EOL;
            }
        }

        $localDisk->deleteDirectory($tmpDir);
        $localDisk->delete($zipFileLocal);
    }

    // /**
    //  * @return array<string>
    //  */
    // private function getJurisdictions(): array
    // {
    //     return [
    //         'ak',
    //         'al',
    //         'ar',
    //         'az',
    //         'ca',
    //         'co',
    //         'ct',
    //         'de',
    //         'fl',
    //         'ga',
    //         'hi',
    //         'ia',
    //         'id',
    //         'il',
    //         'in',
    //         'ks',
    //         'ky',
    //         'la',
    //         'ma',
    //         'md',
    //         'me',
    //         'mi',
    //         'mn',
    //         'mo',
    //         'ms',
    //         'mt',
    //         'nc',
    //         'nd',
    //         'ne',
    //         'nh',
    //         'nj',
    //         'nm',
    //         'nv',
    //         'ny',
    //         'oh',
    //         'ok',
    //         'or',
    //         'pa',
    //         'ri',
    //         'sc',
    //         'sd',
    //         'tn',
    //         'tx',
    //         'uscfr',
    //         'ut',
    //         'va',
    //         'vt',
    //         'wa',
    //         'wi',
    //         'wv',
    //         'wy',
    //     ];
    // }
}
