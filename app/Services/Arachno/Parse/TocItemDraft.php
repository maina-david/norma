<?php

namespace App\Services\Arachno\Parse;

use ArrayAccess;

/**
 * @implements ArrayAccess<string,mixed>
 */
class TocItemDraft implements ArrayAccess
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(protected array $item)
    {
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->item[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->item[$offset];
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->item[(string) $offset] = $value;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->item[$offset]);
    }
}
