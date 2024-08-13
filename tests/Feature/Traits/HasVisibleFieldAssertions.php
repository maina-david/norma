<?php

namespace Tests\Feature\Traits;

use Closure;
use Illuminate\Support\Arr;

trait HasVisibleFieldAssertions
{
    protected function assertSeeVisibleFields($item, $response): mixed
    {
        $value = null;

        foreach (static::visibleFields() as $field) {
            global $value;

            if ($field instanceof Closure) {
                $value = call_user_func($field, $item);
            } else {
                $value = Arr::get($item, $field);
            }
            $response->assertSee($value);
        }

        return $value;
    }

    protected function assertDontSeeVisibleFields($item, $response): mixed
    {
        $value = null;

        foreach (static::visibleFields() as $field) {
            global $value;

            if ($field instanceof Closure) {
                $value = call_user_func($field, $item);
            } else {
                $value = Arr::get($item, $field);
            }
            $response->assertDontSee($value);
        }

        return $value;
    }
}
