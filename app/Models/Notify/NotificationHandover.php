<?php

namespace App\Models\Notify;

use App\Models\AbstractModel;
use App\Models\Workflows\Board;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperNotificationHandover
 */
class NotificationHandover extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'include_meta' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

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
