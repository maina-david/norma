<?php

namespace App\Traits;

trait HandlesUTF8
{
    /**
     * Convert the value to UTF8.
     *
     * @param mixed $value
     *
     * @codeCoverageIgnore
     *
     * @return mixed
     */
    protected function toUTF8(mixed $value): mixed
    {
        return mb_check_encoding($value) ? $value : mb_convert_encoding($value, 'UTF-8');
    }

    /**
     * Convert the key value array to a UTF8 valued array.
     *
     * @param array<string, mixed> $values
     *
     * @codeCoverageIgnore
     *
     * @return array<string, mixed>
     */
    protected function arrayToUTF8(array $values): array
    {
        foreach ($values as $key => $value) {
            $values[$key] = $this->toUTF8($value);
        }

        return $values;
    }
}
