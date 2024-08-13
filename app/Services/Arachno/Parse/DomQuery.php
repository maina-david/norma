<?php

namespace App\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use ArrayAccess;

/**
 * @implements ArrayAccess<string, mixed>
 */
class DomQuery implements ArrayAccess
{
    /**
     * @param array<string, mixed> $query
     */
    public function __construct(protected array $query)
    {
        $this->query = [];
        if (!isset($query['type']) || !$query['type'] instanceof DomQueryType) {
            throw new InvalidQueryException('`type` is required and has to be one of : ' . DomQueryType::implode() . ' - see ' . DomQueryType::class);
        }
        $this->query['type'] = $query['type'];

        if (isset($query['query'])) {
            if ($query['type'] === DomQueryType::NESTED) {
                if (!is_array($query['query'])) {
                    throw new InvalidQueryException('query has to be an array of queries if type is nested');
                }
                $this->query['query'] = [];
                foreach ($query['query'] as $q) {
                    $this->query['query'][] = new self($q);
                }
            } else {
                $this->query['query'] = $query['query'];
            }
        }
        if (isset($query['source'])) {
            if (!$query['source'] instanceof DomQuerySource) {
                throw new InvalidQueryException('`source` has to be one of : ' . DomQuerySource::implode());
            }
            $this->query['source'] = $query['source'];
        }
        if (isset($query['glue'])) {
            $this->query['glue'] = $query['glue'];
        }
        if (isset($query['operator'])) {
            $this->query['operator'] = $query['operator'];
        }
        if (isset($query['wait_for'])) {
            $this->query['wait_for'] = $query['wait_for'];
        }
        if (isset($query['needs_browser'])) {
            $this->query['needs_browser'] = $query['needs_browser'];
        }
        if (isset($query['pre_glue_transforms'])) {
            if (!is_array($query['pre_glue_transforms'])) {
                throw new InvalidQueryException('pre_glue_transforms has to be an array of transforms');
            }
            $this->query['pre_glue_transforms'] = [];
            foreach ($query['pre_glue_transforms'] as $transform) {
                $this->query['pre_glue_transforms'][] = new StringTransform($transform);
            }
        }
        if (isset($query['transforms'])) {
            if (!is_array($query['transforms'])) {
                throw new InvalidQueryException('transforms has to be an array of transforms');
            }
            $this->query['transforms'] = [];
            foreach ($query['transforms'] as $transform) {
                $this->query['transforms'][] = new StringTransform($transform);
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
        return isset($this->query[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->query[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // @codeCoverageIgnoreStart
        $this->query[(string) $offset] = $value;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        // @codeCoverageIgnoreStart
        unset($this->query[$offset]);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return bool
     */
    public function isUrlSource(): bool
    {
        return isset($this->query['source']) && $this->query['source'] === DomQuerySource::URL;
    }

    /**
     * @return bool
     */
    public function isNested(): bool
    {
        return isset($this->query['type']) && $this->query['type'] === DomQueryType::NESTED;
    }

    /**
     * @return bool
     */
    public function isAnchorTextSource(): bool
    {
        return isset($this->query['source']) && $this->query['source'] === DomQuerySource::ANCHOR_TEXT;
    }

    /**
     * @return bool
     */
    public function isTypeRegex(): bool
    {
        return isset($this->query['type']) && $this->query['type'] === DomQueryType::REGEX;
    }

    /**
     * @return bool
     */
    public function isTypeCss(): bool
    {
        return isset($this->query['type']) && $this->query['type'] === DomQueryType::CSS;
    }

    /**
     * @return bool
     */
    public function isTypeJson(): bool
    {
        return isset($this->query['type']) && $this->query['type'] === DomQueryType::JSON;
    }

    /**
     * @return bool
     */
    public function isTypeText(): bool
    {
        return isset($this->query['type']) && $this->query['type'] === DomQueryType::TEXT;
    }

    /**
     * @return bool
     */
    public function isTypeFunc(): bool
    {
        return isset($this->query['type'])
            && $this->query['type'] === DomQueryType::FUNC
            && is_callable($this->query['query']);
    }
}
