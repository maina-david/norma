<?php

namespace App\Services\Arachno\Parse;

use ArrayAccess;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

class DocMetaDto implements ArrayAccess
{
    public ?string $source_unique_id = null;
    public ?string $title = null;
    public ?string $title_translation = null;
    public ?string $language_code = null;
    public ?string $source_url = null;
    public ?string $download_url = null;
    public ?string $work_type = null;
    public ?string $work_number = null;
    public ?string $publication_number = null;
    public ?string $publication_document_number = null;
    public ?string $summary = null;
    public ?string $primary_location = null;
    /** @var array<string>|null */
    public ?array $keywords = null;
    public ?Carbon $work_date = null;
    public ?Carbon $effective_date = null;
    /** @var array<int>|null */
    public ?array $legal_domains = null;

    /**
     * @param array<string,mixed>|null $properties
     */
    public function __construct(?array $properties = null)
    {
        if (!is_null($properties)) {
            foreach ($properties as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return !is_null($this->{$offset});
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->{$offset};
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->{$offset} = $value;
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->{$offset} = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $arr = [];
        foreach (get_class_vars(static::class) as $property => $val) {
            if (is_null($this->{$property})) {
                continue;
            }
            $value = $this->{$property};
            if (in_array($property, ['work_date', 'effective_date'])) {
                $value = $value->format('Y-m-d');
            }
            $arr[$property] = $value;
        }

        return $arr;
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return static
     */
    public function setValues(array $properties): static
    {
        foreach ($properties as $key => $value) {
            if (in_array($key, ['work_date', 'effective_date'])) {
                try {
                    $value = Carbon::createFromFormat('Y-m-d', $value);

                    // @codeCoverageIgnoreStart
                } catch (InvalidFormatException $th) {
                    continue;
                }
                // @codeCoverageIgnoreEnd
            }
            $this->{$key} = $value;
        }

        return $this;
    }
}
