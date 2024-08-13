<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;
use App\Models\Arachno\Pivots\LocationSource;
use App\Models\Collaborators\Collaborator;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperSource
 */
class Source extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    /**
     * associated DB table.
     *
     * @var string
     */
    protected $table = 'corpus_sources';

    /** @var array<string, string> */
    protected $casts = [
        'permission_obtained' => 'boolean',
        'monitoring' => 'boolean',
        'ingestion' => 'boolean',
    ];

    /**
     * @return BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class)
            ->using(LocationSource::class);
    }

    /**
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'owner_id');
    }

    /**
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'manager_id');
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
            'title' => $this->title,
            'source_url' => $this->source_url,
        ];
    }
}
