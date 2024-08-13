<?php

namespace App\Enums\Notify;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static LegalUpdateStatus unread()
 * @method static LegalUpdateStatus read()
 * @method static LegalUpdateStatus readUnderstood()
 */
class LegalUpdateStatus extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'notify.legal_update.status.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'unread' => 1,
            'read' => 2,
            'readUnderstood' => 3,
        ];
    }
}
