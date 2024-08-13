<?php

namespace App\Events\Auth\UserActivity\Files;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;

class PreviewedFile extends UserActivityEvent
{
    public function __construct(protected File $file, User $user, ?Libryo $libryo = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $libryo, $organisation);
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
