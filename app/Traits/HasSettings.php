<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait HasSettings
{
    /**
     * Get the settings column name.
     *
     * @return string
     */
    protected static function settingsColumn(): string
    {
        return 'settings';
    }

    /**
     * Boot the trait.
     */
    public static function bootHasSettings(): void
    {
        static::creating(function (Model $model) {
            $column = static::settingsColumn();
            $model->{$column} = static::defaultSettings();
        });
    }

    /**
     * Get the default settings from the config.
     *
     * @return array<string, mixed>
     */
    public static function defaultSettings(): array
    {
        return config(sprintf('norma.model_settings.%s.defaults', static::class)) ?? [];
    }

    /**
     * Get the casts array.
     *
     * @return array<string, string>
     */
    public function getCasts()
    {
        $casts = parent::getCasts();

        $casts[static::settingsColumn()] = 'json';

        return $casts;
    }

    /**
     * @param string $setting
     * @param mixed  $value
     *
     * @return bool
     */
    public function updateSetting(string $setting, mixed $value): bool
    {
        $settings = $this->settings;

        if (!Arr::has($settings, $setting)) {
            $parts = explode('.', $setting);
            $length = count($parts);

            for ($i = 1; $i <= $length; $i++) {
                $key = implode('.', array_slice($parts, 0, $i));

                if (!Arr::has($settings, $key)) {
                    Arr::set($settings, $key, null);
                }
            }
        }

        if ((is_bool($value) || is_string($value)) && Arr::get($settings, $setting) === $value) {
            return false;
        }

        Arr::set($settings, $setting, $value);
        $data['settings'] = $settings;
        $this->update($data);

        return true;
    }

    /**
     * Get the setting value.
     *
     * @param string $setting
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSetting(string $setting, mixed $default): mixed
    {
        return Arr::get($this->settings, $setting, $default);
    }
}
