<?php

namespace App\Enums;

use ErrorException;
use InvalidArgumentException;

/**
 * @property string|int|bool $value
 */
abstract class Enum
{
    /**
     * @var string|int|bool
     */
    protected string|int|bool $state;

    /** @var string */
    protected static string $langPrefix = '';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string|int,string|int|bool>
     */
    abstract protected static function enums(): array;

    /**
     * Prevent the creation of a new instance.
     *
     * @throws InvalidArgumentException
     */
    final protected function __construct()
    {
    }

    /**
     * Get the enum that is being created.
     *
     * @param string            $name
     * @param array<int,string> $arguments
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public static function __callStatic(string $name, array $arguments): self
    {
        return tap(new static(), function (Enum $instance) use ($name) {
            $enums = $instance::enums();
            $isValue = in_array($name, $enums);
            $isKey = !is_numeric($name) && array_key_exists($name, $enums);

            if (!$isValue && !$isKey) {
                throw new InvalidArgumentException("The provided enum '{$name}' does not exist.");
            }

            $instance->state = $isKey ? $enums[$name] : $name;
        });
    }

    /**
     * Get the value of the enum.
     *
     * @param string $name
     *
     * @throws ErrorException
     *
     * @return string|int|bool
     */
    public function __get(string $name): string|int|bool
    {
        if ($name === 'value') {
            return $this->state;
        }

        // @codeCoverageIgnoreStart
        throw new ErrorException("Unknown property {$name}");
        // @codeCoverageIgnoreEnd
    }

    /**
     * Convert the enum to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Check if the given enum matches the current one.
     *
     * @param Enum|string|int|bool|null $enum
     *
     * @return bool
     */
    public function is(Enum|string|int|bool|null $enum): bool
    {
        $enum = $enum instanceof Enum ? $enum->state : $enum;

        return $enum === $this->state;
    }

    /**
     * Create an enum from the provided value.
     *
     * @param string|int|bool $value
     *
     * @return static
     */
    public static function fromValue(string|int|bool $value): self
    {
        return tap(new static(), function (Enum $instance) use ($value) {
            $enums = array_filter($instance::enums(), fn ($item) => $item === $value);

            if (empty($enums)) {
                throw new InvalidArgumentException("The provided enum value '{$value}' does not exist.");
            }

            $key = array_key_first($enums);

            $instance->state = is_numeric($key)
                ? $value
                : $enums[$key];
        });
    }

    /**
     * Get the valid enums for checks.
     *
     * @return array<string|int,string|int|bool>
     */
    public static function options(): array
    {
        return array_values(static::enums());
    }

    /**
     * Get a list of all enums converted to language.
     *
     * @return array<string|int,mixed>
     */
    public static function lang(): array
    {
        $out = [];

        foreach (static::enums() as $key => $value) {
            /** @var string $result */
            $result = __(static::$langPrefix . $value);

            if (str_contains((string) $value, '.')) {
                $value = (string) $value;

                /** @var array<string,string> $prefix */
                $prefix = __(rtrim(static::$langPrefix, '.'));

                $out[$value] = $prefix[$value] ?? $result;

                continue;
            }

            $out[$value] = $result;
        }

        return $out;
    }

    /**
     * Get a list of all enums to be used for a selector.
     *
     * @param bool $withDefault
     *
     * @return array<string|int,mixed>
     */
    public static function forSelector(bool $withDefault = false): array
    {
        $out = [];

        if ($withDefault) {
            $out[] = ['value' => '--disabled--', 'label' => ''];
        }

        foreach (static::lang() as $key => $value) {
            $out[] = ['value' => $key, 'label' => $value];
        }

        return $out;
    }

    /**
     * Get the translated label for the given enum.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __(static::$langPrefix . $this->value);
    }
}
