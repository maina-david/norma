<?php

namespace App\Enums\Collaborators;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static ApplicationStatus pending()
 * @method static ApplicationStatus approved()
 * @method static ApplicationStatus declined()
 */
class ApplicationStatus extends Enum
{
    protected static string $langPrefix = 'collaborators.collaborator_application.application_status.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'pending' => 1,
            'approved' => 2,
            'declined' => 3,
        ];
    }
}
