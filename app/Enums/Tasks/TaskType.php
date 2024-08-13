<?php

namespace App\Enums\Tasks;

use App\Enums\Enum;

/**
 * @property string $value;
 *
 * @method static TaskType assess()
 * @method static TaskType context()
 * @method static TaskType file()
 * @method static TaskType generic()
 * @method static TaskType requirements()
 * @method static TaskType updates()
 */
class TaskType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'tasks.task.types.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,string>
     */
    protected static function enums(): array
    {
        return [
            'assess' => 'assessment_item_response',
            'context' => 'context_question',
            'file' => 'file',
            'generic' => 'place',
            'requirements' => 'register_item',
            'updates' => 'register_notification',
        ];
    }

    /**
     * @return array<string>
     */
    public static function keys(): array
    {
        return array_keys(static::enums());
    }

    /**
     * @return array<string, string>
     */
    public static function keysLang(): array
    {
        $out = [];
        foreach (static::keys() as $key) {
            /** @var string */
            $trans = __('tasks.task.types.' . $key);
            $out[$key] = $trans;
        }

        return $out;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function toTaskable(string $type): string
    {
        return static::enums()[$type] ?? '';
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $type
     *
     * @return string
     */
    public static function toKey(string $type): string
    {
        return array_flip(static::enums())[$type] ?? '';
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function valueExists(string $value): bool
    {
        return in_array($value, static::enums());
    }
}
