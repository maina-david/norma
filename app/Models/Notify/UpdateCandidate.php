<?php

namespace App\Models\Notify;

use App\Models\AbstractModel;
use App\Models\Corpus\Doc;
use App\Models\Notify\Pivots\NotificationActionUpdateCandidate;
use App\Models\Notify\Pivots\NotificationHandoverUpdateCandidate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperUpdateCandidate
 */
class UpdateCandidate extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $incrementing = false;

    protected $primaryKey = 'doc_id';

    /** @var array<string, string> */
    protected $casts = [
        'affected_available' => 'boolean',
        'affected_legislation' => 'array',
        'justification_status' => 'boolean',
        'manual' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    /**
     * @return BelongsTo
     */
    public function notificationStatusReason(): BelongsTo
    {
        return $this->belongsTo(NotificationStatusReason::class, 'notification_status_reason_id');
    }

    /**
     * @return BelongsToMany
     */
    public function notificationHandovers(): BelongsToMany
    {
        return $this->belongsToMany(NotificationHandover::class, (new NotificationHandoverUpdateCandidate())->getTable(), 'doc_id')
            ->using(NotificationHandoverUpdateCandidate::class)
            ->withPivot(['status', 'meta']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function legislation(): HasMany
    {
        return $this->hasMany(UpdateCandidateLegislation::class, 'doc_id');
    }

    /**
     * @return BelongsToMany
     */
    public function notificationActions(): BelongsToMany
    {
        return $this->belongsToMany(NotificationAction::class, (new NotificationActionUpdateCandidate())->getTable(), 'doc_id')
            ->using(NotificationActionUpdateCandidate::class)
            ->withPivot(['status']);
    }
}
