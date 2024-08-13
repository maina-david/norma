<?php

namespace App\Models\Log;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUserLifecycleActivity
 */
class UserLifecycleActivity extends AbstractModel
{
    protected $table = 'user_lifecycle_activities';

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
