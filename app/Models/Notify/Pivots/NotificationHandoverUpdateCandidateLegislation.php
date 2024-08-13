<?php

namespace App\Models\Notify\Pivots;

use App\Models\Notify\UpdateCandidateLegislation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNotificationHandoverUpdateCandidateLegislation
 */
class NotificationHandoverUpdateCandidateLegislation extends Pivot
{
    /** @var array<string, string> */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legislation(): BelongsTo
    {
        return $this->belongsTo(UpdateCandidateLegislation::class, 'update_candidate_legislation_id');
    }
}
