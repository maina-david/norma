<?php

namespace App\Events\Auth\UserActivity\Folders;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;

class UploadedDocument extends UserActivityEvent
{
    /**
     * @param \App\Models\Storage\My\Folder          $folder
     * @param \App\Models\Storage\My\File            $file
     * @param \App\Models\Auth\User                  $user
     * @param \App\Models\Customer\Libryo|null       $libryo
     * @param \App\Models\Customer\Organisation|null $organisation
     */
    public function __construct(protected Folder $folder, protected File $file, User $user, ?Libryo $libryo = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $libryo, $organisation);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::uploadedDocument();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'folder' => [
                'id' => $this->folder->id,
            ],
            'file' => [
                'id' => $this->file->id,
            ],
        ], $options);
    }
}
