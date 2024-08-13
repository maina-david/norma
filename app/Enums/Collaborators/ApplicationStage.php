<?php

namespace App\Enums\Collaborators;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static ApplicationStage one()
 * @method static ApplicationStage two()
 * @method static ApplicationStage provisional()
 */
class ApplicationStage extends Enum
{
    protected static string $langPrefix = 'collaborators.collaborator_application.application_stage.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'one' => 1,
            'two' => 2,
            'provisional' => 3,
        ];
    }
}
