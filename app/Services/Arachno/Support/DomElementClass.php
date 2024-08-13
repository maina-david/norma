<?php

namespace App\Services\Arachno\Support;

use DOMElement;
use Illuminate\Support\Str;

class DomElementClass
{
    /**
     * @param DOMElement           $el
     * @param array<string>|string $hasClass
     *
     * @return bool
     */
    public static function hasClass(DOMElement $el, array|string $hasClass): bool
    {
        $clssStr = $el->getAttribute('class');
        $classes = explode(' ', $clssStr);
        if (is_array($hasClass)) {
            foreach ($hasClass as $cl) {
                if (in_array($cl, $classes)) {
                    return true;
                }
            }

            return false;
        }

        return in_array($hasClass, $classes);
    }

    /**
     * determines if a given class name matches a given pattern. Asterisks may be used as wildcard values.
     *
     * @param DOMElement $el
     * @param string     $hasClass
     *
     * @return bool
     */
    public static function hasClassLike(DOMElement $el, string $hasClass): bool
    {
        $clssStr = $el->getAttribute('class');
        $classes = explode(' ', $clssStr);
        foreach ($classes as $cl) {
            if (Str::of($cl)->is($hasClass)) {
                return true;
            }
        }

        return false;
    }
}
