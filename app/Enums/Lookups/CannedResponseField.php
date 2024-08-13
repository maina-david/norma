<?php

namespace App\Enums\Lookups;

use App\Enums\Enum;

/**
 * @property string $value;
 *
 * @method static CannedResponseField changesToRegister()
 * @method static CannedResponseField issuingAuthority()
 * @method static CannedResponseField justification()
 * @method static CannedResponseField checkedComment()
 * @method static CannedResponseField notificationStatusReason()
 * @method static CannedResponseField sourceRights()
 */
class CannedResponseField extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'lookups.canned_response.fields.';

    /**
     * Get the allowed enums.
     *
     * @return array<string, string>
     */
    protected static function enums(): array
    {
        return [
            'changesToRegister' => 'changes_to_register',
            'issuingAuthority' => 'issuing_authority',
            'justification' => 'justification',
            'checkedComment' => 'checked_comment',
            'notificationStatusReason' => 'notification_status_reason',
            'sourceRights' => 'source_rights',
        ];
    }
}
