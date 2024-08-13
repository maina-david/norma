<?php

namespace App\Services\Arachno\Parse;

use Illuminate\Support\Str;

class StringTransformer
{
    /**
     * @param string                 $str
     * @param array<StringTransform> $transforms
     *
     * @return string
     */
    public function apply(string $str, array $transforms): string
    {
        $stringable = Str::of($str);

        foreach ($transforms as $transform) {
            if (!$transform instanceof StringTransform) {
                throw new InvalidQueryException('`transforms` has to be an array of `StringTransform` objects');
            }

            $args = $transform['args'] ?? [];
            $functionName = $transform['function']->value;
            $stringable = $stringable->{$functionName}(...$args);
        }

        return (string) $stringable;
    }
}
