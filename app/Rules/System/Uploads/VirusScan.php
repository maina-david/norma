<?php

namespace App\Rules\System\Uploads;

use App\Services\System\Antivirus\ClamAVClient;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VirusScan implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected ClamAVClient $client)
    {
    }

    /**
     * Run the validation rule.
     *
     * @codeCoverageIgnore
     *
     * @param string                                                               $attribute
     * @param mixed                                                                $value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!filter_var(config('antivirus.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        if (!is_array($value)) {
            $result = $this->client->scan($this->getFilePath($value));
            if (!$result->hasPassed()) {
                $fail(__($result->message()));
            }

            return;
        }

        foreach ($value as $file) {
            $result = $this->client->scan($this->getFilePath($file));
            if (!$result->hasPassed()) {
                $fail(__($result->message()));

                return;
            }
        }
    }

    /**
     * Get the filepath from the given item.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $file
     *
     * @return string
     */
    protected function getFilePath(mixed $file): string
    {
        if ($file instanceof UploadedFile) {
            return $file->getRealPath();
        }

        if (is_array($file) && null !== Arr::get($file, 'tmp_name')) {
            return $file['tmp_name'];
        }

        return $file;
    }
}
