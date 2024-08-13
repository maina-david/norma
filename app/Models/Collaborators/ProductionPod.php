<?php

namespace App\Models\Collaborators;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperProductionPod
 */
class ProductionPod extends AbstractModel
{
    protected $table = 'production_pods';

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'owner_id');
    }
}
