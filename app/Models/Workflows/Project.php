<?php

namespace App\Models\Workflows;

use App\Http\ModelFilters\Workflows\ProjectFilter;
use App\Models\AbstractModel;
use App\Models\Arachno\Source;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Group;
use App\Models\Collaborators\ProductionPod;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Pivots\LegalDomainProject;
use App\Models\Workflows\Pivots\ProjectSource;
use App\Models\Workflows\Pivots\ProjectTrackingSpecialist;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperProject
 */
class Project extends AbstractModel
{
    use Filterable;

    protected $table = 'librarian_projects';

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'context_due_date' => 'date',
        'actions_due_date' => 'date',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'owner_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function productionPod(): BelongsTo
    {
        return $this->belongsTo(ProductionPod::class, 'production_pod_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(Source::class, (new ProjectSource())->getTable(), 'project_id', 'source_id')
            ->using(ProjectSource::class);
    }

    public function trackingSpecialists(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, (new ProjectTrackingSpecialist())->getTable(), 'project_id', 'user_id')
            ->using(ProjectTrackingSpecialist::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new LegalDomainProject())->getTable(), 'project_id', 'legal_domain_id')
            ->using(LegalDomainProject::class);
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(ProjectFilter::class);
    }
}
