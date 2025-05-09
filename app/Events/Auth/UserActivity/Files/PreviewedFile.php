<?php

namespace App\Events\Auth\UserActivity\Files;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;

class PreviewedFile extends UserActivityEvent
{
    public function __construct(protected File $file, User $user, ?Norma $norma = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $norma, $organisation);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::previewedFile();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'file' => [
                'id' => $this->file->id,
            ],
        ], $options);
    }
}
