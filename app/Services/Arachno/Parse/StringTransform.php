<?php

namespace App\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\StringTransformFunction;
use ArrayAccess;

/**
 * @implements ArrayAccess<string, mixed>
 */
class StringTransform implements ArrayAccess
{
    /**
     * @param array<string, mixed> $transform
     */
    public function __construct(protected array $transform)
    {
        if (!isset($transform['function']) || !$transform['function'] instanceof StringTransformFunction) {
            throw new InvalidQueryException('`function` is required and has to be an instance of ' . StringTransformFunction::class . ' - ' . json_encode($transform));
        }
        if (isset($transform['args']) && !is_array($transform['args'])) {
            throw new InvalidQueryException('`args` has to be an array of arguments: ' . json_encode($transform));
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->transform[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->transform[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->transform[(string) $offset] = $value;
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->transform[$offset]);
    }
}
