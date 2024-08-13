<?php

namespace App\Models\Notify;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperNotificationStatusReason
 */
class NotificationStatusReason extends AbstractModel
{
    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
        ];
    }
}
