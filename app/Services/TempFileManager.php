<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TempFileManager
{
    /** Number of hours to keep temporary files. */
    protected const PRESERVE_HOURS = 24;

    /**
     * Get the temporary directory to be used.
     *
     * @return string
     */
    public function currentTempDirectory(): string
    {
        return $this->getDirectory() . Carbon::now()->startOfHour()->timestamp;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return config('filesystems.paths.temp') . DIRECTORY_SEPARATOR;
    }

    /**
     * Ensure that the temporary directory exists before performing any storage actions.
     *
     * @return string
     */
    protected function ensureDirectoryExists(): string
    {
        $dir = $this->currentTempDirectory();

        Storage::makeDirectory($dir);

        return $dir;
    }

    /**
     * Temporarily store the uploaded file and return the path.
     *
     * @param UploadedFile $file
     *
     * @return string|false
     */
    public function store(UploadedFile $file): string|bool
    {
        $this->ensureDirectoryExists();

        return Storage::putFile($this->currentTempDirectory(), $file);
    }

    /**
     * @param UploadedFile $file
     *
     * @return string|false
     */
    public function storeWithRandomName(UploadedFile $file): string|false
    {
        $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();

        $dir = $this->ensureDirectoryExists();

        return Storage::putFileAs($dir, $file, $filename);
    }

    /**
     * Get a temporary file if it exists.
     *
     * @param string $path
     *
     * @return File|null
     */
    public function get(string $path): ?File
    {
        if (!Storage::exists($path)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $directories = explode('/', $path);

        $filename = sprintf('%s/working/%s', $this->currentTempDirectory(), array_pop($directories));

        /** @var string $content */
        $content = Storage::get($path);

        Storage::disk('local')->put($filename, $content);

        return new File(storage_path("app/{$filename}"));
    }

    /**
     * Clean up the temp directory.
     *
     * @return void
     */
    public function clean(): void
    {
        $preserve = collect(range(1, static::PRESERVE_HOURS))
            ->map(fn ($hour) => $this->getDirectory() . Carbon::now()->startOfHour()->subHours($hour))
            ->toArray();

        collect(Storage::directories($this->getDirectory()))
            ->filter(fn ($directory) => !in_array($directory, $preserve))
            ->each(fn ($directory) => Storage::deleteDirectory($directory));
    }
}
