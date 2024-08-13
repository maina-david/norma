<?php

namespace App\Traits;

use App\Enums\Enum;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait InteractsWithFilters
{
    /**
     * @param string $filterKey
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @codeCoverageIgnore
     *
     * @return string|array<string, mixed>
     */
    protected function getFilterValue(string $filterKey): string|array
    {
        $multiple = $this->resourceFilters()[$filterKey]['multiple'] ?? false;

        /** @var Request $request */
        $request = request();

        return $request->get($filterKey, $multiple ? [] : '') ?? '';
    }

    /**
     * Get the enum label from the given value.
     *
     * @param class-string<Enum> $enum
     * @param mixed              $value
     *
     * @return string
     */
    protected static function getEnumLabel(string $enum, mixed $value): string
    {
        try {
            return $enum::fromValue($value)->label();
        } catch (InvalidArgumentException $e) {
            if (is_numeric($value) && $value !== (int) $value) {
                return static::getEnumLabel($enum, (int) $value);
            }

            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }
    }
}
