<?php

namespace App\Models\Collaborators;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;

/**
 * @mixin IdeHelperAuditTrail
 */
class AuditTrail extends AbstractModel
{
    use MassPrunable;

    protected $table = 'librarian_audit_trails';

    /** @var array<string, string> */
    protected $casts = [
        'details' => 'array',
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
