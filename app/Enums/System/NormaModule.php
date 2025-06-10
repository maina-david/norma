<?php

namespace App\Enums\System;

use App\Enums\Enum;

/**
 * @property string $value
 *
 * @method static self actions()
 * @method static self comments()
 * @method static self comply()
 * @method static self drives()
 * @method static self keyword_search()
 * @method static self live_chat()
 * @method static self search_requirements_and_drives()
 * @method static self tasks()
 * @method static self update_emails()
 * @method static self updates()
 * @method static self corpus()
 * @method static self dashboard()
 * @method static self sso()
 */
class NormaModule extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'customer.organisation.modules.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<int, string>
     */
    protected static function enums(): array
    {
        return [
            'actions',
            'comments',
            'comply',
            'drives',
            'keyword_search',
            'live_chat',
            'search_requirements_and_drives',
            'tasks',
            'update_emails',
            'updates',
            'corpus',
            'dashboard',
            'sso',
        ];
    }
}
