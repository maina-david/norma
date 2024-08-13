<?php

namespace App\Models\System;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;

/**
 * @mixin IdeHelperApiLog
 */
class ApiLog extends AbstractModel
{
    use MassPrunable;

    /** @var array<string, string> */
    protected $casts = [
        'headers' => 'array',
    ];

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable()
    {
        return static::where('created_at', '<=', now()->subYear());
    }
}
