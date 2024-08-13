<?php

namespace App\Enums\Traits;

trait HasLabels
{
    /**
     * Get the language prefix to be used to generate the language.
     *
     * @return string
     */
    abstract protected static function languagePrefix(): string;

    /**
     * Get the translated label for the given enum.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __(static::languagePrefix() . $this->value);
    }

    /**
     * Get a list of all enums converted to language.
     *
     * @return array<string|int,mixed>
     */
    public static function lang(): array
    {
        $out = [];
        $langPrefix = static::languagePrefix();

        foreach (static::cases() as $case) {
            $value = $case->value;

            /** @var string $result */
            $result = __($langPrefix . $value);

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
            // @codeCoverageIgnoreStart
            $out[] = ['value' => '--disabled--', 'label' => ''];
            // @codeCoverageIgnoreEnd
        }

        foreach (static::lang() as $key => $value) {
            $out[] = ['value' => $key, 'label' => $value];
        }

        return $out;
    }
}
