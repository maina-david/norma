<?php

namespace App\Models\Auth;

use App\Models\AbstractModel;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperIdentityProvider
 */
class IdentityProvider extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'certificate' => 'encrypted',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
