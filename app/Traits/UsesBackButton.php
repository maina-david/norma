<?php

namespace App\Traits;

trait UsesBackButton
{
    /**
     * Get the previous URL in the current stack.
     *
     * @codeCoverageIgnore
     *
     * @param string             $fallback
     * @param array<int, string> $exclude
     *
     * @return string
     */
    protected function getPreviousUrl(string $fallback, array $exclude = []): string
    {
        $prefix = url('/');
        $referer = str_replace($prefix, '', request()->header('referer', ''));
        $current = str_replace($prefix, '', request()->fullUrl());
        $stack = collect(session('_ref_back', []));

        /** @var int $location */
        $location = $stack->search(fn ($value) => explode('?', $value)[0] === explode('?', $referer)[0]);

        if ($location !== false) {
            $stack->splice($location, count($stack));
        }

        if (!empty($referer) && !in_array($referer, $exclude)) {
            $stack->push($referer);
        }

        /** @var int $location */
        $location = $stack->search(fn ($value) => explode('?', $value)[0] === explode('?', $current)[0]);
        if ($location !== false) {
            $stack->splice($location, count($stack));
        }

        session()->put('_ref_back', $stack->toArray());

        /** @var string */
        return $stack->isNotEmpty() ? url($stack->last()) : $fallback;
    }
}
